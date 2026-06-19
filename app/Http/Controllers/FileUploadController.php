<?php
namespace App\Http\Controllers;
use App\Mail\ParticipantNameTagMail;
use App\Mail\AuthMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Carbon\Carbon;

class FileUploadController extends Controller
{
    public function index1(Request $request)
    {
        $event_id = $request->id;
        $event_name = DB::table('events')->where('event_id', $request->id)->value('event_name');

        $data = DB::table('members')->orderBy('created_at', 'DESC')->get();
//dd($data);
        return view('fileupload.index', ['event_name' => $event_name, 'event_id' => $event_id, 'data' => $data]);
    }

    public function index(Request $request)
    {
        $event_id = $request->id;
        $event_name = DB::table('events')->where('event_id', $request->id)->value('event_name');

        // Get search keyword from the request
        $search = $request->input('search');

        // Start the query builder for members
        $query = DB::table('members')->orderBy('created_at', 'DESC');

        // Apply search if a search keyword is provided
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('company_name', 'like', '%' . $search . '%')
                    ->orWhere('participant', 'like', '%' . $search . '%')
                    ->orWhere('email_address', 'like', '%' . $search . '%')
                    ->orWhere('phone_number', 'like', '%' . $search . '%');
//                    ->orWhere('city', 'like', '%' . $search . '%');
            });
        }

        // Paginate results
        $data = $query->paginate(10);

        return view('fileupload.index', [
            'event_name' => $event_name,
            'event_id' => $event_id,
            'data' => $data
        ]);
    }



    /**
     * Imports participant data from an uploaded Excel file.
     *
     * This function validates the uploaded file, processes the data to extract participant details,
     * generates QR codes for reference codes, and performs database operations to insert or update
     * participant and meal coupon records. It also handles sending participant emails and authorization
     * emails. In case of errors, appropriate error messages are returned.
     *
     * @param Request $request The incoming HTTP request containing the uploaded file.
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success message or error messages.
     *
     * NOTE: THIS FUNCTION NEEDS TO BE BROKEN DOWN IN CHUNCKS............
     */
    
    public function importData(Request $request)
{
    $validator = Validator::make($request->all(), [
        'uploaded_file' => 'required|file|mimes:xls,xlsx'
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    set_time_limit(300);

    // Generate approval code
    $approvalCode = collect(range(1, 10))
        ->map(fn () => substr('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(0, 35), 1))
        ->implode('');

    $file = $request->file('uploaded_file');

    $importedCount = 0;
    $failedCount   = 0;
    $errorMessages = [];

    $emailsInFile     = [];
    $referencesInFile = [];

    DB::beginTransaction();

    try {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet       = $spreadsheet->getActiveSheet();
        $rowLimit    = $sheet->getHighestDataRow();

        // Read headers
        $headers = [];
        foreach ($sheet->getRowIterator(1, 1) as $headerRow) {
            foreach ($headerRow->getCellIterator() as $cell) {
                $headers[] = trim($cell->getValue());
            }
        }

        for ($row = 2; $row <= $rowLimit; $row++) {

            $rowValues = [];
            foreach ($sheet->getRowIterator($row, $row) as $dataRow) {
                foreach ($dataRow->getCellIterator() as $cell) {
                    $rowValues[] = trim($cell->getValue());
                }
            }

            if (count(array_filter($rowValues)) === 0) {
                continue;
            }

            $data = array_combine($headers, $rowValues);

            $fullname  = trim($data['Fullname'] ?? '');
            $company   = trim($data['CompanyName'] ?? '');
            $address   = trim($data['Address'] ?? '');
            $mobile    = trim($data['MobileNo'] ?? '');
            $email     = strtolower(trim($data['Email'] ?? ''));
            $reference = trim($data['MemberID'] ?? '');
            $dateRaw   = $data['DateJoined'] ?? null;

            /** ---------------- VALIDATIONS ---------------- */

            if (empty($reference)) {
                $errorMessages[] = "Row {$row}: MemberID is missing.";
                $failedCount++;
                continue;
            }

            if (!preg_match('/^MLS-26-\d+$/', $reference)) {
                $errorMessages[] = "Row {$row}: Invalid MemberID format (expected MLS-26-X).";
                $failedCount++;
                continue;
            }

            if (empty($email)) {
                $errorMessages[] = "Row {$row}: Email is missing.";
                $failedCount++;
                continue;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorMessages[] = "Row {$row}: Invalid email format.";
                $failedCount++;
                continue;
            }

            if (in_array($email, $emailsInFile)) {
                $errorMessages[] = "Row {$row}: Duplicate email in uploaded file.";
                $failedCount++;
                continue;
            }

            if (DB::table('members')->where('email_address', $email)->exists()) {
                $errorMessages[] = "Row {$row}: Email already exists in database.";
                $failedCount++;
                continue;
            }

            if (in_array($reference, $referencesInFile)) {
                $errorMessages[] = "Row {$row}: Duplicate MemberID in uploaded file.";
                $failedCount++;
                continue;
            }

            // Phone number (optional)
            if (!empty($mobile) && strtoupper($mobile) !== 'N/A') {
                if (!preg_match('/^\+?\d{9,15}$/', $mobile)) {
                    $errorMessages[] = "Row {$row}: Invalid phone number format.";
                    $failedCount++;
                    continue;
                }
            } else {
                $mobile = null;
            }

            // DateJoined conversion
            try {
                if (is_numeric($dateRaw)) {
                    $dateJoined = ExcelDate::excelToDateTimeObject($dateRaw)->format('Y-m-d');
                } else {
                    $dateJoined = Carbon::parse($dateRaw)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $errorMessages[] = "Row {$row}: Invalid DateJoined format.";
                $failedCount++;
                continue;
            }

            /** ---------------- INSERT ---------------- */

            DB::table('members')->insert([
                'participant'     => $fullname,
                'company_name'    => $company,
                'address'         => $address,
                'phone_number'    => $mobile,
                'email_address'   => $email,
                'reference_code'  => $reference,
                'datejoined'      => $dateJoined,
                'approval_code'   => $approvalCode,
                'pending_status'  => 'pending approval',
                'status'          => 'Member',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            $emailsInFile[]     = $email;
            $referencesInFile[] = $reference;

            $importedCount++;
        }

        DB::table('authorization_logs')->insert([
            'reference_id' => $approvalCode,
            'requested_by' => auth()->user()->name,
            'status'       => 'pending',
            'description'  => 'Uploaded Excel sheet containing member details',
            'created_at'   => now(),
        ]);

        DB::commit();

        return back()
            ->with('success', "Import completed successfully. Imported: {$importedCount}, Failed: {$failedCount}.")
            ->withErrors($errorMessages);


    } catch (\Exception $e) {
        DB::rollBack();

        return back()->withErrors([
            'exception' => 'An unexpected error occurred during import: ' . $e->getMessage()
        ]);
    }
}


    public function download_meal_coupons(Request $request)
    {

        $event_id = $request->id;
        $participants = DB::table('meal_coupon')
            ->where('meal_coupon.event_id', $event_id)
            ->join('events', 'meal_coupon.event_id', '=', 'events.event_id')
            ->select('events.event_name','events.event_id', 'event_participants.participant')
            ->get(); // Retrieve all participants

        return view('download_meal_coupons2.index', ['participants' => $participants, 'event_id' => $event_id]);

    }

    public function sendEmailAuthorization()
    {
        Log::info('Before sending email'); // Log entry before email sending code


        $getEmails = DB::table('users')->where('user_type','3')->orderBy("id", "desc")->get();

        foreach($getEmails as $participant){

            $data = [
                'participant' => $participant->name,
            ];

            Mail::to($participant->email)->send(new AuthMail($data));
            Log::info('Email sent successfully'); // Log entry after email sending code
        }

    }


    public function sendParticipantEmail($participant)
    {
        Log::info('Before sending email'); // Log entry before email sending code

        if (isset($participant['participant']) && isset($participant['reference_code'])) {
            $data = [
                'participant' => $participant['participant'],
                'reference_code' => $participant['reference_code'],
                'event_id' => $participant['event_id'],
            ];

            // Check if the balance is 0
            if ($participant['balance'] <= 0) {
                Mail::to($participant['email_address'])->send(new ParticipantNameTagMail($data));
                Log::info('Email sent successfully'); // Log entry after email sending code
            } else {
                Log::info('Balance is not 0; email not sent'); // Log entry if balance is not 0
            }
        } else {
            // Handle the case where the required keys are not present in the $participant array
            // Log an error or take appropriate action
        }
    }

    public function broadcastEmails(Request $reference_id)
    {
        Log::info('Before sending email'); // Log entry before email sending code

        //dd($reference_id->reference_id);

        $reference_id = $reference_id->reference_id;

        $getEmails = DB::table('event_participants')->where('approval_code',$reference_id)->orderBy("id", "desc")->get();

        //dd($getEmails);
        foreach($getEmails as $participant){

            $data = [
                'participant' => $participant->participant,
                'reference_code' => $participant->reference_code,
                'event_id' => $participant->event_id,
            ];
            //Mail::to("sarahleemsosa@gmail.com")->send(new ParticipantNameTagMail($data));

            // Check if the balance is 0
            if ($participant->balance <= 0) {
                Mail::to($participant->email_address)->send(new ParticipantNameTagMail($data));
                Log::info('Email sent successfully'); // Log entry after email sending code
            } else {
                Log::info('Balance is not 0; email not sent'); // Log entry if balance is not 0
            }

        }

        DB::table('event_participants')->where(['pending_status' => 'pending approval'],['approval_code'=>$reference_id])->update(['pending_status'=> 'approved' ]);

        DB::table('authorization_logs')->where([ 'reference_id'=>$reference_id])->update(['status'=> 'approved','authorized_by'=>auth()->user()->name]);

        return back()->with('message', 'Action has been approved successfully');


    }

    public function generateUniqueCodes($referenceCode, $count)
    {
        $uniqueCodes = [];
        for ($i = 0; $i < $count; $i++) {
            $uniqueCode = $referenceCode . ($i === 0 ? '' : $i);
            $uniqueCodes[] = [
                'participant_reference_code' => $referenceCode,
                'unique_code' => $uniqueCode,
            ];
        }
        return $uniqueCodes;
    }

    public function showFailedEmails()
    {
        // Get log entries for failed emails, grouped by reference_code
        $failedEmails = DB::table('email_logs')
            ->select('reference_code', 'error_message')
            ->where('level', 'error')
            ->orderBy('id', 'desc')
            ->groupBy('reference_code', 'error_message')
            ->get();

        // Return the view with the failed emails data
        return view('failed_emails', compact('failedEmails'));
    }

}
