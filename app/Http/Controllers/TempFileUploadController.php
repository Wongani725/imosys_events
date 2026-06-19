<?php
namespace App\Http\Controllers;
use App\Mail\ParticipantNameTagMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Jobs\SendParticipantEmails;

class TempFileUploadController extends Controller
{
    public function index(Request $request)
    {
        $event_id = $request->id;
        $event_name = DB::table('events')->where('event_id', $request->id)->value('event_name');

        $data = DB::table('event_participants')->orderBy('reference_code', 'DESC')->paginate(5);
        return view('fileupload.index', ['event_name' => $event_name, 'event_id' => $event_id, 'data' => $data]);
    }

    public function importData(Request $request)
    {
        // Validate the uploaded file
        $validator = Validator::make($request->all(), [
            'uploaded_file' => 'required|file|mimes:xls,xlsx'
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Set the maximum execution time for this request to 5 minutes (adjust as needed)
        set_time_limit(300);

        $the_file = $request->file('uploaded_file');

        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $res = "";
        for ($i = 0; $i < 10; $i++) {
            $res .= $chars[mt_rand(0, strlen($chars)-1)];
        }

        try {
            $spreadsheet = IOFactory::load($the_file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $row_limit = $sheet->getHighestDataRow();
            $column_limit = $sheet->getHighestDataColumn();
            $row_range = range(2, $row_limit);
            $column_range = range('F', $column_limit);

            $participantData = [];
            $mealCoupons = [];
            $event_id = $request->input('event_id');

            foreach ($row_range as $row) {

                $reference = $sheet->getCell('B' . $row)->getValue();
                $balance = $sheet->getCell('AB' . $row)->getValue();
                $extra_meals = $sheet->getCell('Q' . $row)->getValue();
                $total_meals = $sheet->getCell('AG' . $row)->getValue();
                $participantData[] = [
                    'event_id' => $event_id,
                    'participant' => $sheet->getCell('C' . $row)->getValue(),
                    'reference_code' => $reference,
                    'phone_number' => $sheet->getCell('E' . $row)->getValue(),
                    'email_address' => $sheet->getCell('D' . $row)->getValue(),
                    'company_name' => $sheet->getCell('G' . $row)->getValue(),
                    'status' => $sheet->getCell('H' . $row)->getValue(),
                    'approval_code' => $res,
                    'pending_status' => 'pending approval',
                    'attire_type' => $sheet->getCell('I' . $row)->getValue(),
                    'attire_size' => $sheet->getCell('J' . $row)->getValue(),
                    'room_type' => $sheet->getCell('L' . $row)->getValue(),
                    'room_number' => $sheet->getCell('M' . $row)->getValue(),
                    'extra_meals' => $extra_meals,
                    'no_of_extra_bed' => $sheet->getCell('W' . $row)->getValue(),
                    'date_paid' => $sheet->getCell('AD' . $row)->getValue(),
                    'hotel' => $sheet->getCell('K' . $row)->getValue(),
                    'invoice_reference' => $sheet->getCell('F' . $row)->getValue(),
                    'gender' => $sheet->getCell('AE' . $row)->getValue(),
                    'position' => $sheet->getCell('AF' . $row)->getValue(),
                    'extra_person_fees' => $sheet->getCell('AH' . $row)->getValue(),
                    'number_of_extra_person' => $sheet->getCell('AI' . $row)->getValue(),
                    'balance' => $balance,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                /* $this->sendParticipantEmail(last($participantData));*/

                if ($balance == 0) {
                    // Generate a unique code using the reference code
                    $uniqueCode = $reference;

                    // Prepare data for upsert into the meal_coupon table
                    $upsertData = [
                        [
                            'participant_reference_code' => $reference,
                            'unique_code' => $uniqueCode,
                            'total_meals' => $total_meals,
                            'event_id' => $request->input('event_id'),
                        ],
                    ];

                    // Check if there are extra meals
                    if ($extra_meals > 0) {
                        $extraCodes = $this->generateUniqueCodes($reference, $extra_meals + 1);
                        $event_id = $request->input('event_id');

                        foreach ($extraCodes as &$coupon) {
                            if (!empty($coupon['unique_code'])) {
                                $coupon['total_meals'] = 1;
                                $coupon['event_id'] = $event_id;
                            }
                        }

                        $upsertData = array_merge($upsertData, $extraCodes);
                    }

                    DB::table('meal_coupon')->upsert($upsertData, ['participant_reference_code', 'unique_code'], ['total_meals', 'event_id']);

                }

            }

            $headerRows = 1; // Number of header rows in the Excel file
            $rowNumber = $headerRows; // Start row number at the header row number
            $referenceCodes = [];
            $emailAddresses = [];

            foreach ($participantData as $participant) {
                $rowNumber++; // Increment the row number

                // Trim each value in the row and check if all are empty
                $isEmptyRow = true;

                // Check if the reference code already exists
                if (in_array($participant['reference_code'], $referenceCodes)) {
                    $errorMessage = "Error: Duplicate reference_code found for row $rowNumber\n";
                    return back()->withErrors(['exception' => $errorMessage]);
                }

                // Add the reference code and email address to the respective arrays
                $referenceCodes[] = $participant['reference_code'];
                $emailAddresses[] = $participant['email_address'];

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

                // Process the non-empty row here...

                if (empty($participant['reference_code'])) {
                    // Display an error message indicating that the email is empty for the current row
                    $errorMessage="Error: Reference_code is empty for row $rowNumber\n";
                    //                    $errorMessage = "Email is empty for row 8";
                    return back()->withErrors(['exception'=>$errorMessage]);

                }
                // Check if the email field is empty
                if (empty($participant['email_address'])) {
                    // Display an error message indicating that the email is empty for the current row
                    $errorMessage="Error: Email is empty for row $rowNumber\n";
                    //                    $errorMessage = "Email is empty for row 8";
                    return back()->withErrors(['exception'=>$errorMessage]);
                    // return redirect()->route('fileupload.index')->with('error', $errorMessage);
                    //var_dump($participant);
                }


                // Check for invalid reference number format
                $validReferencePattern = '/^ICAM-/';
                if (!preg_match($validReferencePattern, $participant['reference_code'])) {
                    // Display an error message indicating that the reference number is invalid for the current row
                    $errorMessage = "Error: Invalid reference number for row $rowNumber\n";
                    return back()->withErrors(['exception'=>$errorMessage]);
                }


                if (empty($errors)) {

                    // Insert the data into the database
                    DB::table('event_participants')->updateOrInsert(
                        ['reference_code' => $participant['reference_code']],
                        $participant

                    );
                    //return back()->with('message', 'file uploaded successfully');
                    //  return redirect()->back()->withErrors($errors)->withInput();
                }

            }

            // Delete existing meal coupons for the imported reference codes
            $existingReferenceCodes = array_column($mealCoupons, 'participant_reference_code');
            DB::table('meal_coupon')->whereIn('participant_reference_code', $existingReferenceCodes)->delete();
            $event_id = $request->input('event_id');
            foreach ($mealCoupons as &$coupon) {
                if (!empty($coupon['unique_code'])) {
                    $event_id = $request->input('event_id');
                    $coupon['total_meals'] = 5;
                    $coupon['event_id'] = $event_id;
                }
            }
            DB::table('meal_coupon')->upsert($mealCoupons, ['participant_reference_code', 'unique_code'], ['event_id', 'total_meals']);


            $event_name = DB::table('events')->where('event_id', $event_id)->value('event_name');
            $data = DB::table('event_participants')->orderBy('reference_code', 'DESC')->paginate(5);


            DB::table('authorization_logs')->insert([
                'reference_id' => $res,
                'requested_by' => auth()->user()->name,
                'status' => 'pending',
                'description' => 'Uploaded excelsheet containing participants details',
            ]);




            return back()->with('message', 'File Uploaded Successfully and is pending approval');
        } catch (Exception $e) {
            // return view('fileupload.index')->with('error', 'An error occurred while importing the data.');
        }
    }




    public function auth_update_participant(Request $request) {

        $mealCoupons = array();
        $reference_code = $request->input('reference_code');
        $participant = $request->input('participant');
        $meals = $request->input('meals');
        $email_address = $request->input('email_address');
        $phone_number = $request->input('phone_number');
        $company_name = $request->input('company_name');
        $gender = $request->input('gender');
        $status = $request->input('status');
        $position = $request->input('position');
        $attire_type = $request->input('attire_type');
        $attire_size = $request->input('attire_size');
        $hotel = $request->input('hotel');
        $room_type = $request->input('room_type');
        $room_number = $request->input('room_number');
        $extra_meals = $request->input('extra_meals');
        $no_of_extra_bed = $request->input('no_of_extra_bed');
        $date_paid = $request->input('date_paid');
        $invoice_reference = $request->input('invoice_reference');
        $lunch_hotel = $request->input('lunch_hotel');
        $dinner_hotel = $request->input('dinner_hotel');
        $hotel_fees = $request->input('hotel_fees');
        $cost_per_meal = $request->input('cost_per_meal');
        $meals_total_cost = $request->input('meals_total_cost');
        $breakfast_fees = $request->input('breakfast_fees');
        $no_of_breakfast = $request->input('no_of_breakfast');
        $extra_bed = $request->input('extra_bed');
        $total_hotel_extra_fees = $request->input('total_hotel_extra_fees');
        $participation_fees = $request->input('participation_fees');
        $total_amount = $request->input('total_amount');
        $amount_paid = $request->input('amount_paid');
        $balance = $request->input('balance');
        $receipt_number = $request->input('receipt_number');
        $reference_code = $request->input('reference_code');


        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $res = "";
        for ($i = 0; $i < 10; $i++) {
            $res .= $chars[mt_rand(0, strlen($chars)-1)];
        }

        $participantData =[
            'reference_code' => $reference_code,
            'participant' => $request->input('participant'),
            'meals' => $request->input('meals'),
            'email_address' => $request->input('email_address'),
            'phone_number' => $request->input('phone_number'),
            'company_name' => $request->input('company_name'),
            'gender' => $request->input('gender'),
            'status' => $request->input('status'),
            'position' => $request->input('position'),
            'attire_type' => $request->input('attire_type'),
            'attire_size' => $request->input('attire_size'),
            'hotel' => $request->input('hotel'),
            'room_type' => $request->input('room_type'),
            'room_number' => $request->input('room_number'),
            'extra_meals' => $request->input('extra_meals'),
            'no_of_extra_bed' => $request->input('no_of_extra_bed'),
            'date_paid' => $request->input('date_paid'),
            'invoice_reference' => $request->input('invoice_reference'),
            'lunch_hotel' => $request->input('lunch_hotel'),
            'dinner_hotel' => $request->input('dinner_hotel'),
            'hotel_fees' => $request->input('hotel_fees'),
            'cost_per_meal' => $request->input('cost_per_meal'),
            'meals_total_cost' => $request->input('meals_total_cost'),
            'breakfast_fees' => $request->input('breakfast_fees'),
            'no_of_breakfast' => $request->input('no_of_breakfast'),
            'extra_bed' => $request->input('extra_bed'),
            //'pending_status'=>'pending approval',
            //'approval_code'=>$res,
            'total_hotel_extra_fees' => $request->input('total_hotel_extra_fees'),
            'participation_fees' => $request->input('participation_fees'),
            'total_amount' => $request->input('total_amount'),
            'amount_paid' => $request->input('amount_paid'),
            'balance' => $request->input('balance'),
            'receipt_number' => $request->input('receipt_number'),
        ];
        $this->sendParticipantEmail($participantData);
        if ($balance == 0) {
            $uniqueCode = $reference_code;
            $upsertData = [
                [
                    'participant_reference_code' => $reference_code,
                    'unique_code' => $uniqueCode,
                    'total_meals' => $meals,
                    'event_id' => $request->input('event_id'),
                ],
            ];
            if ($extra_meals > 0) {
                $extraCodes = $this->generateUniqueCodes($reference_code, $extra_meals + 0);
                $event_id = $request->input('event_id');

                foreach ($extraCodes as &$coupon) {
                    if (!empty($coupon['unique_code'])) {
                        $coupon['total_meals'] = 1;
                        $coupon['event_id'] = $event_id;
                    }
                }

                $upsertData = array_merge($upsertData, $extraCodes);
            }

            DB::table('meal_coupon')->upsert($upsertData, ['participant_reference_code', 'unique_code'], ['total_meals', 'event_id']);

        }

       // DB::update('update event_participants set participant = ?, email_address = ?, phone_number = ?, company_name = ?, status = ?, attire_type = ?, attire_size = ?, hotel = ?, room_type = ?, room_number = ?, extra_meals = ?, no_of_extra_bed = ?, date_paid = ?, invoice_reference = ?, lunch_hotel = ?, dinner_hotel = ?, hotel_fees = ?, cost_per_meal = ?, meals_total_cost = ?, breakfast_fees = ?, no_of_breakfast = ?, extra_bed = ?, total_hotel_Extra_fees = ?, participation_fees = ?, total_amount = ?, amount_paid = ?, balance = ?, receipt_number = ?,pending_status = ?,approval_code = ? where reference_code = ?',[$participant,$email_address,$phone_number,$company_name,$status,$attire_type,$attire_size,$hotel,$room_type,$room_number,$extra_meals,$no_of_extra_bed,$date_paid,$invoice_reference,$lunch_hotel,$dinner_hotel,$hotel_fees,$cost_per_meal,$meals_total_cost,$breakfast_fees,$no_of_breakfast,$extra_bed,$total_hotel_extra_fees,$participation_fees,$total_amount,$amount_paid,$balance,$receipt_number,'pending approval',$res, $request->id]);
        DB::update('update event_participants set pending_status= ?, participant = ?, email_address = ?, phone_number = ?,meals = ?, company_name = ?, status = ?, attire_type = ?, attire_size = ?, hotel = ?, room_type = ?, room_number = ?, extra_meals = ?, no_of_extra_bed = ?, date_paid = ?, invoice_reference = ?, lunch_hotel = ?, dinner_hotel = ?, hotel_fees = ?, cost_per_meal = ?, meals_total_cost = ?, breakfast_fees = ?, no_of_breakfast = ?, extra_bed = ?, total_hotel_Extra_fees = ?, participation_fees = ?, total_amount = ?, amount_paid = ?, balance = ?, receipt_number = ?,pending_status = ?,approval_code = ? where reference_code = ?',['approved',$participant,$email_address,$phone_number,$meals,$company_name,$status,$attire_type,$attire_size,$hotel,$room_type,$room_number,$extra_meals,$no_of_extra_bed,$date_paid,$invoice_reference,$lunch_hotel,$dinner_hotel,$hotel_fees,$cost_per_meal,$meals_total_cost,$breakfast_fees,$no_of_breakfast,$extra_bed,$total_hotel_extra_fees,$participation_fees,$total_amount,$amount_paid,$balance,$receipt_number,'pending approval',$res, $request->id]);
        DB::update('UPDATE meal_coupon SET total_meals = ? WHERE unique_code = ?', [$request->input('meals'), $request->id]);


        Log::info('Before sending email'); // Log entry before email sending code
        //dd($reference_id->reference_id);


            $data = [
                    'participant_reference_code' => $reference_code,
                    'unique_code' => $uniqueCode,
                    'total_meals' => $meals,
                    'event_id' => $request->input('event_id'),
                ];
            //Mail::to($request->input('email_address'))->send(new ParticipantNameTagMail($data));
            // Check if the balance is 0
            if ($participant->balance == 0) {

               // Mail::to($request->input('email_address'))->send(new ParticipantNameTagMail($data));
                Log::info('Email sent successfully'); // Log entry after email sending code
            } else {
                Log::info('Balance is not 0; email not sent'); // Log entry if balance is not 0
            }


        DB::table('event_participants')->where(['pending_status' => 'pending approval'],['approval_code'=>$reference_id])->update(['pending_status'=> 'approved' ]);
        DB::table('authorization_logs')->where([ 'reference_id'=>$reference_id])->update(['status'=> 'approved','authorized_by'=>auth()->user()->name]);


        return back()->with('message', 'Participant Updated and Approved Successfully');
    }

    public function authorizeParticipant(Request $reference_id)
    {
        Log::info('Before sending email'); // Log entry before email sending code
        //dd($reference_id->reference_id);

        /*
        $reference_id = $reference_id->reference_id;
        $getEmails = DB::table('event_participants')->where('approval_code',$reference_id)->orderBy("id", "desc")->get();

        set_time_limit(1200);

        dd($getEmails);
        //$iterationCount = 0;
        foreach($getEmails as $participant){
           // $iterationCount++;
            $data = [
                'participant' => $participant->participant,
                'reference_code' => $participant->reference_code,
                'event_id' => $participant->event_id,
                // Add other participant data as needed
            ];
            $this->sendParticipantEmail($participant);
            //Mail::to("sarahleemsosa@gmail.com")->send(new ParticipantNameTagMail($data));
            // Check if the balance is 0
            if ($participant->balance <= 0) {
                //dd('whelo');
                echo($participant->email_address.'\n');

               // Mail::to($participant->email_address)->send(new ParticipantNameTagMail($data));
    //          Mail::to("sarahleemsosa@gmail.com")->send(new ParticipantNameTagMail($data));
                Log::info('Email sent successfully'); // Log entry after email sending code
            }
            else {
                Log::info('Balance is not 0; email not sent'); // Log entry if balance is not 0
            }
        }//dd($iterationCount);
        DB::table('event_participants')->where(['pending_status' => 'pending approval'],['approval_code'=>$reference_id])->update(['pending_status'=> 'approved' ]);
        DB::table('authorization_logs')->where([ 'reference_id'=>$reference_id])->update(['status'=> 'approved','authorized_by'=>auth()->user()->name]);

        $text = implode(" , ", $this->invalidEmails);
        //return back()->with('message', "Action has been approved successfully, Failed Emails: $text");

        */

        set_time_limit(10000);
        Log::info('Before sending email'); // Log entry before email sending code
        $reference_id =  $reference_id->reference_id;
       //$reference_id = $request->input('reference_id');
        $getEmails = DB::table('event_participants')->select('participant','email_address','reference_code','event_id', 'balance')->where('approval_code', $reference_id)->orderBy("id", "desc")->get();

        $chunkSize = 20; // Define your chunk size

        //dd($getEmails->chunk($chunkSize));

        // Create an array to store failed emails
        $failedEmails = [];
        // Chunk participants and dispatch email sending jobs
        foreach ($getEmails->chunk($chunkSize) as $chunk) {
            $jobs = [];

            foreach ($chunk as $participant) {
                if ($participant->balance <= 0) {
                    $job_now = new SendParticipantEmails($participant); // Create a job instance
                    dispatch($job_now); // Dispatch the job

                    // No need to check job failure here

                    $jobs[] = $job_now; // Store the job instance in the $jobs array
                } else {
                    Log::info('Balance is not 0; email not sent'); // Log entry if balance is not 0
                }

                foreach ($jobs as $job) {
                    if ($job->hasFailed($participant->email_address)) {
                        $failedEmails[] = $job->getParticipant()->email_address;
                    }
                }
            }


            // Handle failed jobs after the entire chunk has been dispatched




            // Dispatch email sending jobs
           // dispatch($jobs);

            // Update database records, log entries, etc.

            // Handle exceptions
            /*foreach ($jobs as $job) {
                if ($job->hasFailed()) {
                    $failedEmails[] = $job->getParticipant()->email_address;
                }
            }*/
        }

        DB::table('event_participants')
            ->where([
                ['pending_status', '=', 'pending approval'],
                ['approval_code', '=', $reference_id]
            ])
            ->update(['pending_status' => 'approved']);

        //DB::table('event_participants')->where(['pending_status' ,'=', 'pending approval'],['approval_code', '=' ,$reference_id])->update(['pending_status'=> 'approved' ]);
        DB::table('authorization_logs')->where( 'reference_id', $reference_id)->update(['status'=> 'approved','authorized_by'=>auth()->user()->name]);


        // Update pending_status and authorization_logs
        /*DB::table('event_participants')
            ->where(['pending_status' => 'pending approval'], ['approval_code' => $reference_id])
            ->update(['pending_status' => 'approved']);

        DB::table('authorization_logs')
            ->where(['reference_id' => $reference_id])
            ->update(['status' => 'approved', 'authorized_by' => auth()->user()->name]);*/

        $text = implode(" , ", $failedEmails);

        return back()->with('message', "Action has been approved successfully {$text} 1");

    }

    public function updateApproveParticipant(Request $request)
    {
        set_time_limit(10000);
        Log::info('Before sending email'); // Log entry before email sending code
        $reference_id = $request->input('reference_id');
        $getEmails = DB::table('event_participants')->where('approval_code', $reference_id)->orderBy("id", "desc")->get();
        $chunkSize = 20; // Define your chunk size

        // Create an array to store failed emails
        $failedEmails = [];
        // Chunk participants and dispatch email sending jobs
        foreach ($getEmails->chunk($chunkSize) as $chunk) {
            $jobs = [];
            foreach ($chunk as $participant) {
                if ($participant->balance == 0) {
                    $jobs[] = new SendParticipantEmails($participant);
                } else {
                    Log::info('Balance is not 0; email not sent'); // Log entry if balance is not 0
                }
            }

            // Dispatch email sending jobs
            dispatch($jobs);

            // Update database records, log entries, etc.

            // Handle exceptions
            foreach ($jobs as $job) {
                if ($job->hasFailed()) {
                    $failedEmails[] = $job->getParticipant()->email_address;
                }
            }
        }

        // Update pending_status and authorization_logs
        DB::table('event_participants')
            ->where(['pending_status' => 'pending approval'], ['approval_code' => $reference_id])
            ->update(['pending_status' => 'approved']);

        DB::table('authorization_logs')
            ->where(['reference_id' => $reference_id])
            ->update(['status' => 'approved', 'authorized_by' => auth()->user()->name]);

        $text = implode(" , ", $failedEmails);

        return back()->with('message', "Action has been approved successfully {$text} 1");
    }

//    public function updateApproveParticipant(Request $reference_id)
//    {
//        Log::info('Before sending email'); // Log entry before email sending code
//        $reference_id = $reference_id->reference_id;
//        $getEmails = DB::table('event_participants')->where('approval_code',$reference_id)->orderBy("id", "desc")->get();
//        set_time_limit(300);
//        $failedEmails = [];
//        try {
//            foreach ($getEmails as $participant) {
//                $data = [
//                    'participant' => $participant->participant,
//                    'reference_code' => $participant->reference_code,
//                    'qrcode_path' => $participant->qrcode_path,
//                    'event_id' => $participant->event_id,
//                ];
//                if ($participant->balance == 0) {
//                    try {
//                        Mail::to($participant->email_address)->send(new ParticipantNameTagMail($data));
//                    }
//                    catch (\Exception $exception) {
//                        $failedEmails[] = $participant->email_address;
//                    }
//                    Log::info('Email sent successfully'); // Log entry after email sending code
//                } else {
//                    Log::info('Balance is not 0; email not sent'); // Log entry if balance is not 0
//                }
//            }
//        } catch (\Exception $e) {
//            Log::error('Error sending emails: ' . $e->getMessage());
//        }
//        DB::table('event_participants')->where(['pending_status' => 'pending approval'],['approval_code'=>$reference_id])->update(['pending_status'=> 'approved' ]);
//        DB::table('authorization_logs')->where([ 'reference_id'=>$reference_id])->update(['status'=> 'approved','authorized_by'=>auth()->user()->name]);
//        $text = implode(" , ", $failedEmails);
//        return back()->with('message', "Action has been approved successfully {$text} 1");
//    }


    public function declineParticipant(Request $reference_id)
    {
        Log::info('Before Declining request'); // Log entry before email sending code
        //dd($reference_id->reference_id);
        $reference_id = $reference_id->reference_id;

        DB::table('event_participants')->where(['pending_status' => 'pending approval'],['approval_code'=>$reference_id])->delete();
        DB::table('authorization_logs')->where([ 'reference_id'=>$reference_id])->update(['status'=> 'declined','authorized_by'=>auth()->user()->name]);


        $values = DB::table('authorization_logs')->orderBy('created_at','asc')->get();

       // return view('authorization.logs');

        return view('authorization.logs', compact('values'))->with('message', 'Action has been declined successfully');

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


    public function previewParticipant(Request $reference_id)
    {

        $reference_id = $reference_id->reference_id;
        // Get log entries for failed emails, grouped by reference_code
        $participant_details = DB::table('event_participants')
            ->where( 'approval_code', $reference_id)
            ->where('pending_status','pending approval')
            ->orderBy('participant', 'desc')
            ->get();
            //;

        $event_name = DB::table('events')->where('event_id','ICAM-LK_2023')->value('event_name');

        //$participant_details = $participant_details->where('pending_status','pending approval')->get();
        //dd($event_name);
        // Return the view with the failed emails data
        return view('authorization.previewParticipant', compact('reference_id','participant_details', 'event_name'));
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

//    public function sendParticipantEmail($participant)
//    {
//        Log::info('Before sending email'); // Log entry before email sending code
//
//        if (isset($participant['participant']) && isset($participant['reference_code'])) {
//            $data = [
//                'participant' => $participant['participant'],
//                'reference_code' => $participant['reference_code'],
//                'qrcode_path' => $participant['qrcode_path'],
//                'event_id' => $participant['event_id'],
//                // Add other participant data as needed
//            ];
//
//            // Check if the balance is 0
//            if ($participant['balance'] == 0) {
//                Mail::to($participant['email_address'])->send(new ParticipantNameTagMail($data));
//                Log::info('Email sent successfully'); // Log entry after email sending code
//            } else {
//                Log::info('Balance is not 0; email not sent'); // Log entry if balance is not 0
//            }
//        } else {
//            // Handle the case where the required keys are not present in the $participant array
//            // Log an error or take appropriate action
//        }
//    }

    public function sendParticipantEmail($participant)
    {
        Log::info('Before sending email'); // Log entry before email sending code

        $email = trim($participant->email_address);

        // Check if the participant object has the expected properties
        if (isset($participant->participant) && isset($participant->reference_code) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $data = [
                'participant' => $participant->participant,
                'reference_code' => $participant->reference_code,
                'event_id' => $participant->event_id,
            ];
            // Send email using the ParticipantNameTagMail class or other logic
            try {
                Mail::to($email)->send(new ParticipantNameTagMail($data));
                Log::info('Email sent successfully');
            }
            catch (\Swift_TransportException $e) {
                $this->invalidEmails[] = $email;
                Log::error('Email sending error: ' . $e->getMessage());
//                echo $e->getMessage();
            }
//            catch (Exception $e) {
//                $this->invalidEmails[] = $email;
//                Log::error('Email sending error: ' . $e->getMessage());
//            }
        } else {
            $this->invalidEmails[] = $email;
            Log::error('Participant object is missing expected properties or has an invalid email address');
        }
    }

    protected $invalidEmails = [];



}
