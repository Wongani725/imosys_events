<?php

namespace App\Http\Controllers;

use App\Mail\EmailCertificates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EmailCertificatesController extends Controller
{
    public function index(Request $request)
    {
        $event_id = $request->id;
        $event_name = DB::table('events')->where('event_id', $request->id)->value('event_name');

        $data = DB::table('event_participants')->orderBy('reference_code', 'DESC')->paginate(5);
        return view('email_certificates.index', ['event_name' => $event_name, 'event_id' => $event_id, 'data' => $data]);
    }

    public function importData(Request $request)
    {
        $this->validate($request, [
            'uploaded_file' => 'required|file|mimes:xls,xlsx'
        ]);

        $the_file = $request->file('uploaded_file');

        try {
            $spreadsheet = IOFactory::load($the_file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $row_limit = $sheet->getHighestDataRow();
            $column_limit = $sheet->getHighestDataColumn();
            $row_range = range(7, $row_limit);
            $column_range = range('F', $column_limit);

            $participantData = [];
            $mealCoupons = [];

            $event_id = $request->input('event_id');

            foreach ($row_range as $row) {

                $reference = $sheet->getCell('B' . $row)->getValue();

                $balance = $sheet->getCell('AB' . $row)->getValue();
                $extra_meals = $sheet->getCell('Q' . $row)->getValue();

                // Generate the reference code field
                $participantData[] = [
                    'event_id' => $event_id,
                    'participant' => $sheet->getCell('C' . $row)->getValue(),
                    'reference_code' => $reference,
                    'email_address' => $sheet->getCell('D' . $row)->getValue(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $this->sendParticipantEmail(last($participantData));

                $this->sendParticipantEmail(last($participantData));
            }



            $headerRows = 6; // Number of header rows in the Excel file
            $rowNumber = $headerRows; // Start row number at the header row number

            foreach ($participantData as $participant) {
                $rowNumber++; // Increment the row number

                // Trim each value in the row and check if all are empty
                $isEmptyRow = true;
                foreach ($participant as $value) {
                    if (trim($value) !== '') {
                        $isEmptyRow = false;
                        break;
                    }
                }

                // Check if the row is empty or contains only whitespace
                if ($isEmptyRow) {
                    // Skip the empty row
                    continue;
                }
                if (empty($participant['reference_code'])) {
                    // Display an error message indicating that the email is empty for the current row
                    $errorMessage="Error: Reference_code is empty for row $rowNumber\n";
//                    $errorMessage = "Email is empty for row 8";
                    return back()->with('message', $errorMessage);
                    // return redirect()->route('fileupload.index')->with('error', $errorMessage);
                    //var_dump($participant);
                }
                // Check if the email field is empty
                if (empty($participant['email_address'])) {
                    // Display an error message indicating that the email is empty for the current row
                    $errorMessage="Error: Email is empty for row $rowNumber\n";
//                    $errorMessage = "Email is empty for row 8";
                    return back()->with('message', $errorMessage);
                    // return redirect()->route('fileupload.index')->with('error', $errorMessage);
                    //var_dump($participant);
                }

//                // Insert the data into the database
//                DB::table('event_participants')->updateOrInsert(
//                    ['reference_code' => $participant['reference_code']],
//                    $participant
//
//                );
            }


            // ...

            // Check if there are any validation errors
            if (!empty($errors)) {
                return back()->with('message', 'success');
//                    return redirect()->back()->withErrors($errors)->withInput();
            }

            if (empty($errors)) {
                return back()->with('message', 'file uploaded successfully');
//                    return redirect()->back()->withErrors($errors)->withInput();
            }

        } catch (Exception $e) {
            // return view('fileupload.index')->with('error', 'An error occurred while importing the data.');
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

            Mail::to($participant['email_address'])->send(new EmailCertificates($data));

            Log::info('Email sent successfully'); // Log entry after email sending code
        } else {
            // Handle the case where the required keys are not present in the $participant array
            // Log an error or take appropriate action
        }
    }
    public function show_certificate($id1, $id2)
    {
        $reference_code = $id1;




        $participant = DB::table('event_participants')->WHERE('reference_code', '=', $id1)
            ->join('events', 'event_participants.event_id', '=', 'events.event_id')
            ->select('events.event_name', 'events.event_id','events.theme','events.start_date', 'events.end_date','events.certificate_background', 'event_participants.status','event_participants.company_name', 'event_participants.reference_code', 'event_participants.participant','events.event_venue')
            ->first(); // Retrieve only the first participant
        //return view('view_participant2.index', compact('participant'));
        return view('view_certificate2.index', compact('participant'));
    }

    public function download_certificates(Request $request)
    {
        $event_id = $request->id;
        $participants = DB::table('event_participants')
            ->where('event_participants.event_id', $event_id)
            ->join('events', 'event_participants.event_id', '=', 'events.event_id')
            ->select('events.event_name','events.theme','events.event_id', 'events.start_date', 'events.end_date', 'events.certificate_background', 'event_participants.status', 'event_participants.company_name', 'event_participants.reference_code', 'event_participants.participant', 'events.event_venue')
            ->get(); // Retrieve all participants

        $event_programme = DB::table('event_programme')
            ->where('event_programme.event_id', $event_id)
            ->select('event_programme.session_description','event_programme.session_date','event_programme.start_time')
            ->get();


        return view('download_certificates.index', ['participants' => $participants, 'event_programme' => $event_programme,'event_id' => $event_id]);

        // return view('download_name_tags.index', compact('participants'));
    }


}
