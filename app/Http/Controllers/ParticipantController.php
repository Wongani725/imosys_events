<?php
namespace App\Http\Controllers;

use App\Helpers\JMealsInterface;
use App\Mail\Evaluation;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\MealCoupon;
use App\Models\User;
use Exception;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\IOFactory;
// Remove the duplicate DB import statement below
// App\Http\Controllers\DB;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Validator;
use App\Models\Event;

use Illuminate\Http\Request; // Import the correct Request class from Illuminate\Http

use DB; // Keep this import statement to use the DB class

use App\Mail\userMail;
use App\Mail\ParticipantNameTagMail;
use App\Mail\AuthMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\Hotel;

class ParticipantController extends PrintJobController
{
    //view participants
    public function index(Request $request)
    {
        $event_id = $request->id;
        $search = $request->input('search');
        $hotels = Hotel::where('event_id', $event_id)->get();


        try {
            // Base query
            $query = Participant::where('event_id', $event_id);
            // Search functionality
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('reference_code', 'like', "%{$search}%")
                        ->orWhere('participant', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%")
                        ->orWhere('email_address', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
                });
            }

            // Paginate instead of take()
            $participants = $query->orderBy('created_at', 'desc')
//                ->get();
//            dd($participants);
                ->paginate(10);

            if ($participants->isEmpty()) {
//                throw new \Exception("No content available");
            }
        } catch (\Exception $exception) {
            return redirect()->back()->withInput()->withErrors([
                "exception" => $exception->getMessage()
            ]);
        }

        return view("view_participants.index", [
            'participants' => $participants,
            'event_id' => $event_id,
            'hotels' => $hotels
        ]);
    }

    # Data
    public function sendEmail(Request $request) {
        $validationRules = [
            'email'  => 'required|email',
            'reference'  => 'required|string',
        ];

        $validator = Validator::make($request->all(), $validationRules);
        if($validator->fails()) {
            $validation_error = Helper::FirstValidationError($validator->errors()->toArray());
            return Helper::APIResponse(0, "Invalid data, {$validation_error}",HTTP_UNPROCESSABLE, ['errors'=> $validator->errors()]);
        }

        try {
            try {
                $participant = Participant::where("reference_code", trim($request->reference))->first();
            }
            catch (Exception $exception) {
                throw new Exception("Failed to find participant data");
            }

            if(empty($participant)) {
                throw new Exception("Participant record not found");
            }

            $email = "{$request->email}";

            $data = [
                'participant' => $participant->participant,
                'reference_code' => $participant->reference_code,
                'event_id' => $participant->event_id,
            ];

            if ($participant->balance <= 0) {
                try {
//                set_time_limit(10000);
                    Mail::to(trim($request->email))->send(new ParticipantNameTagMail($data));
                } catch (\Exception $exception) {
                    // Handle email sending exception
                    // You can log the exception or perform any necessary action
                    Log::error('Error sending email to ' . $email . ': ' . $exception->getMessage());
                }
            }
        }
        catch (Exception $exception) {
            return Helper::APIResponse(0, "Invalid data, {$exception->getMessage()}",HTTP_FAILED, ['errors'=> $exception->getMessage()]);
        }

        return Helper::APIResponse(1, "email sent to $email",HTTP_SUCCESS, ['payload'=> $request->all()]);
    }

    public function sendEmailEvaluation(Request $request) {
        $validationRules = [
            'email'  => 'required|email',
            'reference'  => 'required|string',
        ];

        $validator = Validator::make($request->all(), $validationRules);
        if($validator->fails()) {
            $validation_error = Helper::FirstValidationError($validator->errors()->toArray());
            return Helper::APIResponse(0, "Invalid data, {$validation_error}",HTTP_UNPROCESSABLE, ['errors'=> $validator->errors()]);
        }

        try {
            try {
                $participant = Participant::where("reference_code", trim($request->reference))->first();

                if ($participant) {
                    // Update the email_address column
                    $participant->update(['email_address' => $request->email]);
                }

            }
            catch (Exception $exception) {
                throw new Exception("Failed to find participant data");
            }

            if(empty($participant)) {
                throw new Exception("Participant record not found");
            }

            $email = "{$request->email}";

            $data = [
                'Name' => $participant->participant,
                'Reference_Code' => $participant->reference_code,
//                'qrcode_path' => $participant->qrcode_path,
                'Event_Id' => $participant->event_id,
            ];

            if ($participant->balance <= 0) {
                try {
//                set_time_limit(10000);
                    Mail::to(trim($request->email))->send(new Evaluation($data));
                } catch (\Exception $exception) {
                    // Handle email sending exception
                    // You can log the exception or perform any necessary action
                    Log::error('Error sending email to ' . $email . ': ' . $exception->getMessage());
                }
            }
        }
        catch (Exception $exception) {
            return Helper::APIResponse(0, "Invalid data, {$exception->getMessage()}",HTTP_FAILED, ['errors'=> $exception->getMessage()]);
        }

        return Helper::APIResponse(1, "email sent to $email",HTTP_SUCCESS, ['payload'=> $request->all()]);
    }
    //add attendant
    public function add_attendant(Request $id)
    {
        $event_id = $id->id;
        $event_name = DB::table('events')->where('event_id', ($id->id))->value('event_name');
        return view('add_attendant.index', ['event_name' => $event_name, 'event_id' => $event_id]);
    }

    //add participant
    public function add_participant(Request $id)
    {
        $event_id = $id->id;
        $event_name = DB::table('events')->where('event_id', ($id->id))->value('event_name');
        return view('add_participant.index', ['event_name' => $event_name, 'event_id' => $event_id]);
    }
    public function add_attendant2(Request $request)
    {
        DB::beginTransaction();
        try {
            $reference_code = $request->input('reference_code');
            $attendant_name = $request->input('attendant_name');
            $email = $request->input('email');
            $password = '$2y$10$lWm.1E9.T/A6FaZmjlWUFulSYdG207JAIs3QovTJZ2LLmBuaoob5e';
            $total_web_logins = '0';
            $total_mobile_app_logins = '0';
            $user_type = $request->input('user_type');

            $participantData[] = [
                'attendant_name' => $attendant_name,
                'email' => $email,
            ];
            $this->sendParticipantEmail2(last($participantData));
            // Determine the numeric value for user_type based on selected option
            if ($user_type === 'Viewer') {
                $user_type_value = 1;
            } elseif ($user_type === 'Initiator') {
                $user_type_value = 2;
            } elseif ($user_type === 'Super Admin') {
                $user_type_value = 3;
            } else {
                // Handle unexpected user type (optional)
                $user_type_value = null; // Or set a default value
            }

            $data = array(
                'name' => $attendant_name,
                'email' => $email,
                'password' => $password,
                'total_web_logins' => $total_web_logins,
                'total_mobile_app_logins' => $total_mobile_app_logins,
                'user_type' => $user_type_value,
            );

            $user = User::create($data);
            $user->assignRole('Event Attendant');

            DB::commit();
        }
        catch (Exception $exception) {
            DB::rollBack();
            return  redirect()->back()->withInput()->withErrors(['exception'=> $exception->getMessage()]);
        }

        return back()->with('message', 'User Added Successfully');
    }

//    public function add_attendant2(Request $request)
//    {
//        DB::beginTransaction();
//        try {
//            $reference_code = $request->input('reference_code');
//            $attendant_name = $request->input('attendant_name');
//            $email = $request->input('email');
//            $password = '$2y$10$lWm.1E9.T/A6FaZmjlWUFulSYdG207JAIs3QovTJZ2LLmBuaoob5e';
//            $total_web_logins = '5';
//            $total_mobile_app_logins = '5';
//            $user_type = $request->input('reference_code');;
//
//            $data = array(
//                'name' => $attendant_name,
//                'email' => $email,
//                'password' => $password,
//                'total_web_logins' => $total_web_logins,
//                'total_mobile_app_logins' => $total_mobile_app_logins,
//                'user_type' => $user_type,
//            );
//
//            $user = User::create($data);
//            $user->assignRole('Event Attendant');
//
//            DB::commit();
//        }
//        catch (Exception $exception) {
//            DB::rollBack();
//            return  redirect()->back()->withInput()->withErrors(['exception'=> $exception->getMessage()]);
//        }
//        return back()->with('message', 'User Added Successfully');
//    }

    public function PrintParticipantMealCoupons(Request $request)
    {
        $reference_code = $request->input('reference_code');

        $registration = DB::table('i_participant_event_registrations')
            ->where('participant_id', $reference_code)
            ->first();

        if ($registration) {
            $dateScanned = date('Y-m-d'); // Assuming you want to include the current date
            return Helper::APIResponse(1, "Success: Reference code found", HTTP_SUCCESS, ['date' => $dateScanned]);
        } else {
            return Helper::APIResponse(0, "Reference code not found", HTTP_FAILED, []);
        }
    }
    public function add_participant2(Request $request)
    {
        // Step 1: Validate form input
        $request->validate([
            'participant'   => 'required|string|max:255',
            'email_address' => 'nullable|email|max:255',
            'company_name'  => 'required|string|max:255',
            'gender'        => 'required|in:Male,Female',
            'status'        => 'required|in:Member,Non Member,Student',
            'event_id'      => 'required',
            'phone_number'  => 'nullable|string|max:20',
        ]);

        // Step 2: Determine meal count
        $specialCompanies = [
            'ECAMA Secretariat',
            'ECAMA Executive Committee',
            'ECAMA Panelist',
            'ECAMA Presenter',
            'ECAMA Rapporteur',
            'ECAMA Board Member',
            'ECAMA Board Chair',
            'African Economic Research Consortium',
            'iMoSyS',
        ];

        $totalMeals = in_array(trim($request->company_name), $specialCompanies, true) ? 2 : 2;
//dd($totalMeals);
        // Step 3: Generate unique reference code
        do {
            $reference_code = 'MLS-26-WI-' . strtoupper(Str::random(3)) . '-' . strtoupper(Str::random(3));
        } while (DB::table('event_participants')->where('reference_code', $reference_code)->exists());

        // Step 4: Generate unique approval code
        $approval_code = Helper::DBUniqueValue("event_participants", "approval_code");

        // Step 5: Prepare participant data
        $data = [
            'reference_code' => $reference_code,
            'participant'    => $request->participant,
            'phone_number'   => $request->phone_number,
            'email_address'  => $request->email_address,
            'company_name'   => $request->company_name,
            'gender'         => $request->gender,
            'status'         => $request->status,
            'approval_code'  => $approval_code,
            'pending_status' => 'pending approval',
            'meals'          => $totalMeals,
            'balance'        => 0,
            'invoice_reference' => 'N/A',
            'event_id'       => $request->event_id,
            'type'           => 'walkin',
            'total_amount'   => 1600000,
            'amount_paid'    => 1600000,
            'date_paid'      => now(),
            'updated_at'      => now(),
        ];

        DB::table('event_participants')->insert($data);

        // Step 6: Create meal coupon if fully paid
        if ($data['balance'] <= 0) {
            $mealCoupon = [
                'participant_reference_code' => $reference_code,
                'unique_code'                => $reference_code,
                'total_meals'                => $totalMeals,
                'event_id'                   => $request->event_id,
            ];

            DB::table('meal_coupon')->upsert(
                [$mealCoupon],
                ['participant_reference_code', 'unique_code'],
                ['total_meals', 'event_id']
            );
        }

        // Step 7: Log creation
        DB::table('authorization_logs')->insert([
            'reference_id' => $approval_code,
            'requested_by' => auth()->user()->name,
            'status'       => 'pending',
            'description'  => 'Participant created pending approval',
            'created_at'   => now(),
        ]);

        // Step 9: Send confirmation email
        $this->sendParticipantEmail($data);

        // Step 10: Return success message
        return back()->with('message', 'Participant Added Successfully');
    }


    /*public function sendEmailAuthorization()
    {
        Log::info('Before sending email'); // Log entry before email sending code


            $getEmails = DB::table('users')->where('user_type','3')->orderBy("id", "desc")->get();

            foreach($getEmails as $participant){

                    $data = [
                        'participant' => $participant->name,
                    ];

                    Mail::to($participant->email)->send(new authMail($data));
                    Log::info('Email sent successfully'); // Log entry after email sending code
            }

    }*/



    public function getTotalParticipantsByHotel(Request $request)
    {
        $eventID = $request->input('event_id');

        $redeemedCoupons = DB::table('event_participants')
            ->select('hotel', DB::raw('COUNT(*) as total'))
            ->where('event_id', $eventID)
            ->groupBy('hotel')
            ->get();

        $message = "Total participants per hotel for event ID $eventID";

        return response()->json(['msg' => $message, 'Total participants per hotel' => $redeemedCoupons]);
    }

    public function getRedeemedCouponsByHotel(Request $request)
    {
        $eventID = $request->input('event_id');

        $redeemedCoupons = DB::table('meal_scans_per_day')
            ->select('hotel_name', DB::raw('COUNT(*) as total'))
            ->where('event_id', $eventID)
            ->groupBy('hotel_name')
            ->get();

        $message = "Redeemed coupons per hotel for event ID $eventID";

        return response()->json(['msg' => $message, 'redeemed_coupons' => $redeemedCoupons]);
    }

//delete participant
    public function delete_participant($id) {
        DB::delete('delete from event_participants where reference_code = ?',[$id]);
        return back()->with('message', 'Participant deleted Successfully');
    }

    //edit participant
    public function edit_participant($id)
    {
        $data = DB::select('select * from event_participants where reference_code = ?', [$id]);
        return view('edit_participant.index', ['data' => $data]);
    }

    public function update_participant(Request $request) {
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


        $participantData =[
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
            'total_hotel_extra_fees' => $request->input('total_hotel_extra_fees'),
            'participation_fees' => $request->input('participation_fees'),
            'total_amount' => $request->input('total_amount'),
            'amount_paid' => $request->input('amount_paid'),
            'balance' => $request->input('balance'),
            'receipt_number' => $request->input('receipt_number'),
        ];

        DB::update('UPDATE event_participants SET
        participant = ?, meals = ?, email_address = ?, phone_number = ?, company_name = ?,gender = ?,
        status = ?, position = ?, attire_type = ?, attire_size = ?, hotel = ?, room_type = ?,
        room_number = ?, extra_meals = ?, no_of_extra_bed = ?, date_paid = ?, invoice_reference = ?,
        lunch_hotel = ?, dinner_hotel = ?, hotel_fees = ?, cost_per_meal = ?, meals_total_cost = ?,
        breakfast_fees = ?, no_of_breakfast = ?, extra_bed = ?, total_hotel_extra_fees = ?,
        participation_fees = ?, total_amount = ?, amount_paid = ?, balance = ?, receipt_number = ?
        WHERE reference_code = ?',
            [
                $participantData['participant'],
                $participantData['meals'],
                $participantData['email_address'],
                $participantData['phone_number'],
                $participantData['company_name'],
                $participantData['gender'],
                $participantData['status'],
                $participantData['position'],
                $participantData['attire_type'],
                $participantData['attire_size'],
                $participantData['hotel'],
                $participantData['room_type'],
                $participantData['room_number'],
                $participantData['extra_meals'],
                $participantData['no_of_extra_bed'],
                $participantData['date_paid'],
                $participantData['invoice_reference'],
                $participantData['lunch_hotel'],
                $participantData['dinner_hotel'],
                $participantData['hotel_fees'],
                $participantData['cost_per_meal'],
                $participantData['meals_total_cost'],
                $participantData['breakfast_fees'],
                $participantData['no_of_breakfast'],
                $participantData['extra_bed'],
                $participantData['total_hotel_extra_fees'],
                $participantData['participation_fees'],
                $participantData['total_amount'],
                $participantData['amount_paid'],
                $participantData['balance'],
                $participantData['receipt_number'],
                $reference_code // Assuming $reference_code holds the value for reference_code
            ]
        );

        DB::update('UPDATE meal_coupon SET total_meals = ? WHERE unique_code = ?', [$request->input('meals'), $request->id]);
        $this->sendParticipantEmail(last($participantData));

        if ($balance <= 0) {
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
                        $coupon['total_meals'] = 5;
                        $coupon['event_id'] = $event_id;
                    }
                }

                $upsertData = array_merge($upsertData, $extraCodes);
            }

            DB::table('meal_coupon')->upsert($upsertData, ['participant_reference_code', 'unique_code'], ['total_meals', 'event_id']);

        }

        DB::table('meal_coupon')->insert($mealCoupons);

        return back()->with('message', 'Participant Updated Successfully');
    }

    //show participant details
    public function show_participant($id1, $id2)
    {
        $event = Event::where('event_id', $id2)->first();
        if (!$event) abort(404);

        $image = $event->background_image ?? 'images/default_bg.png';

        $reference_code = $id1;
        $mealCoupons = DB::table('meal_coupon')
            ->where('participant_reference_code', $reference_code)
            ->where('event_id', $id2)
            ->where('status', '!=', 'main')
            ->get();

        $participant = DB::table('event_participants')->WHERE('reference_code', '=', $id1)
            ->join('events', 'event_participants.event_id', '=', 'events.event_id')
            ->select('events.event_name','events.event_id','events.theme', 'events.start_date', 'events.end_date','event_participants.status','event_participants.company_name', 'event_participants.reference_code', 'event_participants.participant','events.event_venue')
            ->first();
        if (!$participant) abort(404);

        $event_id = $participant->event_id;
        return view('view_participant.index', compact('participant','mealCoupons', 'event_id', 'image', 'event'));
    }

    public function show_participant2($reference_code, $event_id)
    {
        $event = Event::where('event_id', $event_id)->firstOrFail();
        $image = $event->background_image ?? 'images/default_bg.png';

        $mealCoupons = DB::table('meal_coupon')
            ->where('participant_reference_code', $reference_code)
            ->where('event_id', $event_id)
            ->where('status', '!=', 'main')
            ->get();

        $participant = DB::table('event_participants')
            ->where('reference_code', $reference_code)
            ->where('event_participants.event_id', $event_id)
            ->join('events', 'event_participants.event_id', '=', 'events.event_id')
            ->select('events.event_name','events.event_id','events.theme','events.start_date','events.end_date','events.background_image','event_participants.status','event_participants.company_name','event_participants.reference_code','event_participants.participant','events.event_venue')
            ->first();

        if (!$participant) abort(404);
        $participant->image = $image;

        $documents = \App\Models\EventDocument::where('event_id', $event_id)->get();
        $programPdf = $event->program_pdf ?? null;

        return view('view_participant2.index', compact('participant', 'mealCoupons', 'documents', 'programPdf'));
    }

    public function showParticipantConsolidated($reference_code)
    {
        $participants = DB::table('event_participants')
            ->where('reference_code', $reference_code)
            ->join('events', 'event_participants.event_id', '=', 'events.event_id')
            ->select('events.event_name','events.event_id','events.theme','events.start_date','events.end_date','events.background_image','event_participants.status','event_participants.company_name','event_participants.reference_code','event_participants.participant','events.event_venue')
            ->get();

        if ($participants->isEmpty()) abort(404);

        $eventId = request('event_id', $participants->first()->event_id);
        $current = $participants->firstWhere('event_id', $eventId);
        if (!$current) $current = $participants->first();

        $current->image = $current->background_image ?? 'images/default_bg.png';

        $mealCoupons = DB::table('meal_coupon')
            ->where('participant_reference_code', $reference_code)
            ->where('event_id', $current->event_id)
            ->where('status', '!=', 'main')
            ->get();

        $documents = \App\Models\EventDocument::where('event_id', $current->event_id)->get();
        $programPdf = \App\Models\Event::where('event_id', $current->event_id)->value('program_pdf');

        $allEvents = $participants->map(fn($p) => ['event_id' => $p->event_id, 'event_name' => $p->event_name]);

        return view('view_participant2.consolidated', compact('current', 'participants', 'mealCoupons', 'documents', 'programPdf', 'allEvents', 'reference_code'));
    }

    public function downloadNameTagsPdf($event_id, Request $request)
    {
        set_time_limit(0);
        $event = Event::where('event_id', $event_id)->firstOrFail();

        $limit = $request->query('limit');
        $page = (int)($request->query('page', 1));
        if ($page < 1) $page = 1;

        $query = Participant::where('event_id', $event_id)
            ->orderBy('id', 'desc');

        if ($limit && is_numeric($limit)) {
            $query->limit((int)$limit);
            $query->offset(($page - 1) * (int)$limit);
        }

        $participants = $query->get();

        if ($participants->isEmpty()) {
            return back()->with('error', 'No participants found.');
        }

        $backgroundImage = $event->background_image
            ? asset($event->background_image)
            : asset('images/default_bg.png');

        $eventName = $event->event_name;

        $pdf = Pdf::loadView('pdf.nametags', compact('participants', 'backgroundImage', 'eventName'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('nametags_' . $event_id . '.pdf');
    }

    public function nameTagsView(Request $request)
    {
        $event_id = $request->query('event_id');
        $event = Event::where('event_id', $event_id)->firstOrFail();

        $query = Participant::where('event_participants.event_id', $event_id)
            ->join('events', 'event_participants.event_id', '=', 'events.event_id')
            ->select('event_participants.*', 'events.event_name', 'events.background_image');

        $startingId = (int)($request->query('starting_id', 0));
        if ($startingId > 0) {
            $query->where('event_participants.id', '>=', $startingId);
        }

        $participants = $query->orderBy('event_participants.id', 'desc')->get();
        $image = $event->background_image ?? 'images/default_bg.png';
        $totalParticipants = $participants->count();
        $programPdf = $event->program_pdf;

        return view('admin.name-tags.index', compact('event', 'participants', 'image', 'totalParticipants', 'event_id', 'programPdf'));
    }

    public function view_event_resources($reference_code)
    {
        $participants = Participant::where('reference_code', $reference_code)->get();
        if ($participants->isEmpty()) abort(404);

        $events = Event::whereIn('event_id', $participants->pluck('event_id'))->get();
        $documents = \App\Models\EventDocument::whereIn('event_id', $events->pluck('event_id'))->get();
        $programmes = DB::table('event_programme')->whereIn('event_id', $events->pluck('event_id'))->get();
        $firstParticipant = $participants->first();

        return view('participant_resources', compact('participants', 'events', 'documents', 'programmes', 'firstParticipant'));
    }

    public function view_certificate($id1, $id2)
    {
        $participant = DB::table('event_participants')->WHERE('reference_code', '=', $id1)
            ->join('events', 'event_participants.event_id', '=', 'events.event_id')
            ->select('events.event_name','events.event_id','events.theme', 'events.start_date', 'events.end_date','event_participants.status','event_participants.company_name', 'event_participants.reference_code', 'event_participants.participant','events.event_venue', 'events.certificate_background')
            ->first();
        return view('view_certificate.index', compact('participant'));
    }

    public function downloadCertificatePdf($reference_code, $event_id)
    {
        $participant = DB::table('event_participants')
            ->where('event_participants.reference_code', $reference_code)
            ->where('event_participants.event_id', $event_id)
            ->join('events', 'event_participants.event_id', '=', 'events.event_id')
            ->select('events.event_name','events.event_id','events.theme', 'events.start_date', 'events.end_date','event_participants.status','event_participants.company_name', 'event_participants.reference_code', 'event_participants.participant','events.event_venue', 'events.certificate_background')
            ->first();

        if (!$participant) {
            return back()->with('error', 'Participant not found.');
        }

        $pdf = Pdf::loadView('pdf.certificate', compact('participant'));
        $pdf->setPaper('a4', 'landscape');

        $filename = 'certificate_' . $participant->reference_code . '.pdf';
        return $pdf->download($filename);
    }

    //    public function download_name_tags(Request $id)
    //    {$event_id = $id->id;
    //        $participant = DB::table('event_participants')->WHERE('event_id', '=', $id)
    //            ->join('events', 'event_participants.event_id', '=', 'events.event_id')
    //            ->select('events.event_name', 'events.start_date', 'events.end_date','event_participants.status','event_participants.company_name', 'event_participants.reference_code', 'event_participants.participant','event_participants.qrcode_path','events.event_venue');
    //          //  ->first(); // Retrieve only the first participant
    //        return view('download_name_tags.index', compact('participant'));
    //    }


    public function download_meal_couponss(Request $request)
    {


        $reference_code = DB::table('event_participants')->pluck('reference_code')->toArray();


        $mealCoupons = DB::table('meal_coupon')->where('participant_reference_code', $reference_code)->take(5)->get();
        // $mealCoupons = DB::table('meal_coupon')->where('participant_reference_code', $reference_code)->get();

        $mealCoupons = DB::table('meal_coupon')->get();

      //  $mealCoupons = DB::table('meal_coupon')->get();

// Get all the participant reference codes from the meal coupons
        $participantReferenceCodes = $mealCoupons->pluck('participant_reference_code')->all();

// Fetch the participant details based on the reference codes
        $participants = Participant::whereIn('reference_code', $participantReferenceCodes)->get();

        $event_id = $request->id;
        $participants = DB::table('event_participants')
            ->where('event_participants.event_id', $event_id)
            ->join('events', 'event_participants.event_id', '=', 'events.event_id')
            ->select('events.event_name','events.theme','events.event_id', 'events.start_date','event_participants.company_name', 'events.end_date','event_participants.status', 'event_participants.reference_code', 'event_participants.participant', 'events.event_venue')
            ->get(); // Retrieve all participants

        $event_programme = DB::table('event_programme')
            ->where('event_programme.event_id', $event_id)
            ->select('event_programme.session_description','event_programme.session_date','event_programme.start_time')
            ->get();


        // Assuming you have the variables $participants, $event_programme, $event_id available in your controller.

        return view('download_meal_couponss.index', [
            'participants' => $participants,
            'event_programme' => $event_programme,
            'event_id' => $event_id,
            'mealCoupons' => $mealCoupons,
        ]);
// return view('download_name_tags.index', compact('participants'));
    }

    public function download_name_tags(Request $request)
    {
        return $this->downloadNameTagsPdf($request->id);
    }


//APIs
//Attendance registration
    public function register(Request $request, $id)
    {
        // Find the user by unique ID
        $user = Participant::where('reference_code', $id)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->registered = true;
        $user->save();
        // Registration logic here, you can update the user's information or perform any other actions

        return response()->json(['message' => 'User registered successfully']);
    }



    public function auth_update_participant(Request $request)
    {
        $request->validate([
            'id' => 'required|string|exists:event_participants,reference_code',
            'participant' => 'required|string',
            'email_address' => 'nullable|email',
            'phone_number' => 'nullable|string',
            'company_name' => 'nullable|string',
            'status' => 'nullable|string|in:Member,Non Member',
            'position' => 'nullable|string',
            'gender' => 'nullable|string|in:Male,Female',
            // Add more validation rules as needed
        ]);

        DB::table('event_participants')
            ->where('reference_code', $request->input('id'))
            ->update([
                'participant' => $request->input('participant'),
                'email_address' => $request->input('email_address'),
                'phone_number' => $request->input('phone_number'),
                'company_name' => $request->input('company_name'),
                'status' => $request->input('status'),
                'position' => $request->input('position'),
                'gender' => $request->input('gender'),
            ]);

        return back()->with('message', 'Participant has been successfully updated.');
    }


    public function sendEmailAuthorization()
    {
        Log::info('Before sending email'); // Log entry before email sending code


            $getEmails = DB::table('users')->where('user_type','3')->orderBy("id", "desc")->get();

            foreach($getEmails as $participant){

                    $data = [
                        'participant' => $participant->name,
                    ];

                    Mail::to($participant->email)->send(new authMail($data));
                    Log::info('Email sent successfully'); // Log entry after email sending code
            }

    }

    public function sendParticipantEmail2($participant)
    {

            $data = [
                'email' => $participant['email'],
                'name' => $participant['attendant_name'],
            ];
                Mail::to($participant['email'])->send(new userMail($data));
    }
    public function userRole()
    {

        return view('event_dashboard.dashboard');

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

    public function generateUniqueCodes($referenceCode, $count)
    {
        $uniqueCodes = [];
        for ($i = 1; $i <= $count; $i++) {
            //$uniqueCode = $referenceCode . '_' . Str::random(10);
            $uniqueCode = $referenceCode . '_' . $i;
            $uniqueCodes[] = [
                'participant_reference_code' => $referenceCode,
                'unique_code' => $uniqueCode,
            ];
        }
        return $uniqueCodes;
    }

}
