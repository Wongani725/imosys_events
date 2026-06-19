<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use DB;
use Exception;
use App\Models\User;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Models\AlertMessage;
use App\Models\Event;
use App\Models\Participant;
use App\Models\AttendanceRegistration;
use App\Models\EventSession;
use App\Jobs\sendProgrammeEmails;
use App\Mail\ParticipantNameTagMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\AutoMail;
use PhpOffice\PhpSpreadsheet\IOFactory;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Models\EventPrices;

use App\Models\Hotel;

class EventController extends PrintJobController
{
    public function getSessions(Request $request)
    {
        $event = Event::orderBy('created_at', 'desc')->first();

        if (!$event) {
            return Helper::APIResponse(0, 'No events found.', HTTP_NOT_FOUND, []);
        }

        $event_sessions = DB::table('event_sessions')->where('event_id', $event->event_id)->get();

        if ($event_sessions->isEmpty()) {
            return Helper::APIResponse(1, 'No sessions found for the latest event.', HTTP_NOT_FOUND, []);
        }

        return Helper::APIResponse(1, 'Event sessions retrieved successfully.', HTTP_SUCCESS, $event_sessions->toArray());
    }


    //view events
    public function index()
    {
        $data = DB::table('events')
            ->select('event_id', 'event_type', 'event_status', 'start_date', 'end_date', 'event_name', 'event_venue', 'event_gps_coordinates')
            ->orderBy('start_date', 'desc')
            ->get();
        return view("events.index", ['data' => $data]);
    }

    //add event
    public function add_event()
    {
        return view('add_event.index');
    }

    public function add_event2(Request $request)
    {
        $request->validate([
            'event_id' => 'required|unique:events,event_id',
            'event_name' => 'required',
            'event_theme' => 'required',
            'event_status' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'booking_start_time' => 'required|date',
            'booking_end_time' => 'required|date|after_or_equal:booking_start_time',
            'event_venue' => 'required',
            'event_gps_coordinates' => 'nullable',
            'event_type' => 'nullable|in:governance,main',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:15000',
            'certificate_background' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:15000',
            'program_pdf' => 'nullable|mimes:pdf,jpeg,png,jpg|max:20000',
        ]);

        $event = new Event();
        $event->event_id = $request->event_id;
        $event->event_name = $request->event_name;
        $event->theme = $request->event_theme;
        $event->event_status = $request->event_status;
        $event->event_type = $request->event_type;
        $event->start_date = $request->start_date;
        $event->end_date = $request->end_date;
        $event->booking_start_time = $request->booking_start_time;
        $event->booking_end_time = $request->booking_end_time;
        $event->event_venue = $request->event_venue;
        $event->event_gps_coordinates = $request->event_gps_coordinates;

        if ($request->hasFile('background_image')) {
            $image = $request->file('background_image');
            $imageName = time().'_bg.'.$image->getClientOriginalExtension();
            $image->move(public_path('event_assets'), $imageName);
            $event->background_image = 'event_assets/' . $imageName;
        }

        if ($request->hasFile('certificate_background')) {
            $cert = $request->file('certificate_background');
            $certName = time().'_cert.'.$cert->getClientOriginalExtension();
            $cert->move(public_path('event_assets'), $certName);
            $event->certificate_background = 'event_assets/' . $certName;
        }

        if ($request->hasFile('program_pdf')) {
            $pdf = $request->file('program_pdf');
            $pdfName = time().'_prog.'.$pdf->getClientOriginalExtension();
            $pdf->move(public_path('event_assets'), $pdfName);
            $event->program_pdf = 'event_assets/' . $pdfName;
        }

        $event->save();

        return redirect()->route('events')->with('message', 'Event added Successfully');

    }

    //delete event
    public function delete_event($id)
    {
        DB::delete('delete from events where event_id = ?', [$id]);
        return back()->with('message', 'Event deleted Successfully');
    }

    //edit event
    public function edit_event($id)
    {
        $data = DB::select('select * from events where event_id = ?', [$id]);
//        dd($data);
        return view('update_events.index', ['data' => $data]);
    }

    public function update_event(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,event_id',
            'event_name' => 'required|string|max:255',
            'event_theme' => 'nullable|string|max:255',
            'event_status' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'booking_start_time' => 'required|date',
            'booking_end_time' => 'required|date|after_or_equal:booking_start_time',
            'event_venue' => 'nullable|string|max:255',
            'venue' => 'nullable|string|max:255',
            'event_gps_coordinates' => 'nullable|string|max:255',
            'event_type' => 'nullable|in:governance,main',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'certificate_background' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'program_pdf' => 'nullable|mimes:pdf,jpeg,png,jpg|max:20000',
            'name_tag_padding_top' => 'nullable|integer|min:0|max:600',
            'name_tag_qr_top' => 'nullable|integer|min:0|max:600',
        ]);

        $event = Event::where('event_id', $request->event_id)->first();
        if (!$event) {
            return back()->withErrors(['message' => 'Event not found']);
        }

        $updateData = [
            'event_name' => $request->event_name,
            'theme' => $request->event_theme,
            'event_type' => $request->event_type ?? $event->event_type,
            'event_status' => $request->event_status,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'booking_start_time' => $request->booking_start_time,
            'booking_end_time' => $request->booking_end_time,
            'event_venue' => $request->event_venue,
            'event_gps_coordinates' => $request->event_gps_coordinates,
            'venue' => $request->venue ?? $event->venue,
            'name_tag_padding_top' => $request->input('name_tag_padding_top', $event->name_tag_padding_top ?? 283),
            'name_tag_qr_top' => $request->input('name_tag_qr_top', $event->name_tag_qr_top ?? 120),
        ];

        if ($request->hasFile('background_image')) {
            $image = $request->file('background_image');
            $imageName = time().'_bg.'.$image->getClientOriginalExtension();
            $image->move(public_path('event_assets'), $imageName);
            $updateData['background_image'] = 'event_assets/' . $imageName;
        }

        if ($request->hasFile('certificate_background')) {
            $cert = $request->file('certificate_background');
            $certName = time().'_cert.'.$cert->getClientOriginalExtension();
            $cert->move(public_path('event_assets'), $certName);
            $updateData['certificate_background'] = 'event_assets/' . $certName;
        }

        if ($request->hasFile('program_pdf')) {
            $pdf = $request->file('program_pdf');
            $pdfName = time().'_prog.'.$pdf->getClientOriginalExtension();
            $pdf->move(public_path('event_assets'), $pdfName);
            $updateData['program_pdf'] = 'event_assets/' . $pdfName;
        }

        DB::table('events')->where('event_id', $request->event_id)->update($updateData);

        return back()->with('message', 'Event Updated Successfully');
    }

    public function add_programme(Request $request, $id)
    {

        if ($request->hasFile('icam_programme') && $request->file('icam_programme')->isValid()) {
            try {
                $file = $request->file('icam_programme');

                $imageName = $id . '_programme.png';

                $imagesfolder = 'background_images';
                $destinationPath = public_path($imagesfolder);

                if (file_exists($destinationPath . '/' . $imageName)) {
                    // Delete the existing file
                    unlink($destinationPath . '/' . $imageName);
                }

                $upload = $file->move($destinationPath, $imageName);

                // Flash a success message to the session
                return redirect()->back()->with('success', 'Event updated successfully!');
            } catch (Exception $e) {
                // Handle any exceptions if needed
                return redirect()->back()->with('error', 'Error uploading image: ' . $e->getMessage());
            }
        }

        // Return a success message if the image upload is not applicable to the request
        return view('UploadProgramme.index', ['id' => $id]);

    }


    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);



        if ($request->file('image')->isValid()) {
            //dd($request->all());

            try {

                $file = $request->image;
                $imageName = time() . '.' . $request->image->getClientOriginalExtension();


                $imagesfolder='background_images';
                $destinationPath = public_path( $imagesfolder);
                $upload=$file->move($destinationPath,$imageName);
                return redirect()->back()->with('success', 'Image uploaded successfully!');
                //$request->image->storeAs('public/background_images', file);

            }catch (Exception $e){

                return redirect()->back()->with('error', 'Error uploading image.');
            }


            // Save the image path in the database or use it directly as required
            // e.g., $nameTag->background_path = 'images/' . $imageName;
            // Remember to import the required model and use it as needed


        }


    }

    public function view_hotels(Request $id)
    {$event_id = $id->id;
        try {
            // Get participant list from the database
            $hotels = DB::table('hotel')->where('event_id', $id->id)->get();

//            if ($hotels->isEmpty()) {
//                throw new Exception("No content available");
//            }
        } catch (Exception $exception) {
            return redirect()->back()->withInput()->withErrors(["exception" => "{$exception->getMessage()}"]);
        }

        return view("view_hotels.index", ['hotels' => $hotels, 'event_id' => $event_id]);
    }

    public function add_hotel2(Request $request)
    {
        $event_id = $request->input('event_id');
        $name = $request->input('name');
        $quantity = (int)($request->input('quantity') ?? 0);
        $extra_price = $request->input('extra_price');
        $venue_type = $request->input('venue_type');
        $latitudes = $request->input('latitudes');
        $longitudes = $request->input('longitudes');

        $data = [
            'event_id' => $event_id,
            'name' => $name,
            'quantity' => $quantity,
            'available_count' => $quantity,
            'booked_count' => 0,
            'venue_type' => $venue_type,
            'extra_price' => $extra_price,
            'latitudes' => $latitudes,
            'longitudes' => $longitudes,
        ];
        DB::table('hotel')->insert($data);

        return back()->with('message', 'Hotel Added Successfully');
    }
    public function edit_hotel($id)
    {
        $data = DB::select('select * from hotel where id = ?', [$id]);
        return view('edit_hotel.index', ['data' => $data]);
    }
    public function add_hotel(Request $id)
    {
        $event_id = $id->id;
        $event = Event::where('event_id', $event_id)->first();
        return view('add_hotel.index', compact('event_id', 'event'));
    }

    public function delete_hotel($id) {
        DB::delete('delete from hotel where id = ?',[$id]);
        return back()->with('message', 'Hotel deleted Successfully');
    }

    public function update_hotel(Request $request) {
        $id = $request->input('id');
        $name = $request->input('name');
        $quantity = (int)($request->input('quantity') ?? 0);
        $venue_type = $request->input('venue_type');
        $extra_price = $request->input('extra_price');

        $hotel = DB::table('hotel')->where('id', $id)->first();
        if ($hotel) {
            $diff = $quantity - ($hotel->quantity ?? 0);
            $newAvailable = max(0, ($hotel->available_count ?? 0) + $diff);
            DB::update(
                'update hotel set name = ?, quantity = ?, available_count = ?, venue_type = ?, extra_price = ? where id = ?',
                [$name, $quantity, $newAvailable, $venue_type, $extra_price, $id]
            );
        }

        return back()->with('message', 'Hotel Updated Successfully');
    }

//Sessions
    //view sessions



    public function show_programme(Request $request)
    {
        $event_id = $request->id1;
        $event_name = DB::table('events')->where('event_id', $request->id)->value('event_name');

        $data = DB::table('event_participants')->orderBy('reference_code', 'DESC')->paginate(5);

        $programmeData = DB::table('event_programme')->where('event_id', $event_id)->get();


        return view('ShowProgramme.index', ['event_name' => $event_name, 'event_id' => $event_id,'programmeData' => $programmeData, 'data' => $data]);
    }



    public function importProgram(Request $request, $id)
    {
        $event_id = $request->input('event_id');
        $event_name = $request->input('event_name');
        $request->validate([
            'icam_programme' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',

        ]);
        if ($request->file(['icam_programme'])->isValid()) {  //EDITED**********
            //dd($request->all());
            try {
                $fileAdvert = $request->icam_programme;
                $advert=$id . '_programme';
                //$advert="ICAM Programme-01";
                $imageNameAdvert = $advert . '.' . 'png';
                $imagesfolderAdvert = '/background_images';
                $destinationPathAdvert = public_path($imagesfolderAdvert);
                $uploadAdvert = $fileAdvert->move($destinationPathAdvert, $imageNameAdvert);

                $getEmails = DB::table('event_participants')->select('participant','balance','email_address', 'event_id','email_address', 'reference_code')->where('pending_status', 'approved')->get();
                $chunkSize = 20; // Define your chunk size
                $failedEmails = [];
                foreach ($getEmails->chunk($chunkSize) as $chunk) {
                    $jobs = [];
                    foreach ($chunk as $participant) {
                        if ($participant->balance <= 0) {
                            $job_now = new sendProgrammeEmails($participant); // Create a job instance
                            dispatch($job_now); // Dispatch the job

                            // No need to check job failure here

                            $jobs[] = $job_now; // Store the job instance in the $jobs array
                            // dd($jobs);
                        } else {
                            Log::info('Balance is not 0; email not sent'); // Log entry if balance is not 0
                        }
                        foreach ($jobs as $job) {
                            if ($job->hasFailed($participant->email_address)) {
                                $failedEmails[] = $job->getParticipant()->email_address;
                            }
                        }
                    }
                }

//                $eventParticipants = DB::table('event_participants')->get();
//
//                foreach ($eventParticipants as $participant) {
//                    $this->sendParticipantEmail($participant);
//                }
            }


            catch (Exception $e){
                //return redirect()->back()->with('error', 'Error uploading image.');
            }
        }

        //return redirect()->back()->with('error', 'Error uploading image.');
        return back()->with('message', 'Programme Uploaded Successfully');
    }

    public function sendParticipantEmail($participant)
    {
        Log::info('Before sending email'); // Log entry before email sending code

        // Convert the $participant object to an array (for stdClass objects)
        $participantArray = is_array($participant) ? $participant : (array)$participant;

        if (isset($participantArray['participant']) && isset($participantArray['reference_code'])) {
            $data = [
                'participant' => $participantArray['participant'],
                'reference_code' => $participantArray['reference_code'],
                'event_id' => $participantArray['event_id'],
            ];

            // Check if the balance is 0
            if ($participantArray['balance'] == 0) {
                // Include the program file as an attachment
                $filePath = public_path('background_images/icam_programme.png');
                Mail::to($participantArray['email_address'])->send(new AutoMail($data, $filePath));
                Log::info('Email sent successfully'); // Log entry after email sending code
            } else {
                Log::info('Balance is not 0; email not sent'); // Log entry if balance is not 0
            }
        } else {
            // Handle the case where the required properties are not present on the $participant object
            // Log an error or take appropriate action
        }
    }



    public function view_sessions(Request $id)
    {
        $event_id = $id->id;
        try {
            # Get event sessions list from database
            $sessions = EventSession::all()->where('event_id', $id->id);

            if (empty($sessions)) {
                throw new Exception("No content available");
            }
        } catch (Exception $exception) {
            return redirect()->back()->withInput()->withErrors(["exception" => "{$exception->getMessage()}"]);
        }
        return view("view_sessions.index", ['sessions' => $sessions, 'event_id' => $event_id]);
    }

    //add session
    public function add_session(Request $id)
    {
        $event_id = $id->id;
        $event_name = DB::table('events')->where('event_id', ($id->id))->value('event_name');
        return view('add_session.index', ['event_name' => $event_name, 'event_id' => $event_id]);
    }

    public function add_session2(Request $request)
    {
        $start_time = $request->input('start_time');
        $end_time = $request->input('end_time');
        $session_date = $request->input('session_date');
        $description = $request->input('description');

        $event_id = $request->input('event_id');

        $data = array('start_time' => $start_time, "end_time" => $end_time,"session_date" => $session_date, "description" => $description, "event_id" => $event_id);
        DB::table('event_sessions')->insert($data);

        return back()->with('message', 'Event Session Added Successfully');
    }

    //delete session
    public function delete_session($id) {
        DB::delete('delete from event_sessions where session_id = ?',[$id]);
        return back()->with('message', 'Session deleted Successfully');
    }

    //edit session
    public function edit_session($id)
    {
        $data = DB::select('select * from event_sessions where session_id = ?', [$id]);
        return view('edit_session.index', ['data' => $data]);
    }

    public function update_session(Request $request) {

        $session_id = $request->input('session_id');
        $session_date = $request->input('session_date');
        $start_time = $request->input('start_time');
        $end_time = $request->input('end_time');
        $description = $request->input('description');

        DB::table('event_sessions')->where('session_id', $session_id)->update([
            'session_date' => $session_date,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'description' => $description,
        ]);
        return back()->with('message', 'Session Updated Successfully');
    }



//APIs
    public function listAPI(Request $request)
    {
        $event = Event::orderBy('created_at', 'desc')->first();

        if ($event) {
            $currentDate = now();
            $startDate = Carbon::parse($event->start_date)->startOfDay();
            $endDate = Carbon::parse($event->end_date)->endOfDay();

            // Check if today is between start and end (inclusive)
            $status = $currentDate->between($startDate, $endDate) ? 'Open' : 'Closed';

            $eventArray = $event->toArray();
            $eventArray['status'] = $status;

            return Helper::APIResponse(1, "Events retrieved", HTTP_SUCCESS, $eventArray);
        }

        return Helper::APIResponse(0, "No events found", HTTP_NOT_FOUND, []);
    }

    public function allInitialRegistrations (Request $request){


        try{
//            $user = $request->user();
            $data = DB::table('i_participant_event_registrations')->get();
        }
        catch (Exception $exception) {
            return Helper::APIResponse(0, $exception->getMessage() , HTTP_FAILED, [
                'error' => $exception->getMessage()
            ]);
        }

        $data = compact('data');
        return Helper::APIResponse(1, "Initial participants registrations retrieved", HTTP_SUCCESS, $data);
    }

    public function allMealCoupons(Request $request){


        try{
//            $user = $request->user();
            // $data = DB::table('meal_scans_per_day')->get();

            $data = DB::table('meal_scans_per_day')
                ->whereIn(
                    DB::raw("(unique_code, CONCAT(date, ' ', time))"),
                    function ($query) {
                        $query->select(DB::raw("unique_code, MAX(CONCAT(date, ' ', time))"))
                            ->from('meal_scans_per_day')
                            ->groupBy('unique_code');
                    }
                )
                ->get();

        }
        catch (Exception $exception) {
            return Helper::APIResponse(0, $exception->getMessage() , HTTP_FAILED, [
                'error' => $exception->getMessage()
            ]);
        }

        $data = compact('data');
        return Helper::APIResponse(1, "Meal coupon data retrieved", HTTP_SUCCESS, $data);
    }

       public function upcomingEvents(Request $request)
    {
        try {
            $now = now();

            $upcomingEvent = DB::table('upcoming_events as ue')
                ->join('events as e', 'ue.event_id', '=', 'e.event_id')
                ->select(
                    'ue.event_name',
                    'ue.event_id',
                    'ue.session_id',
                    'ue.session_description',
                    'ue.adjusted_start_time',
                    'ue.session_date',
                    'ue.start_time',
                    'ue.end_time',
                    'e.start_date',
                    'e.end_date'
                )
                ->first();

            if ($upcomingEvent) {
                $upcomingEvent->event_status = $now->between($upcomingEvent->start_date, $upcomingEvent->end_date) ? 'open' : 'closed';
                unset($upcomingEvent->start_date, $upcomingEvent->end_date);
            }

            $upcoming_events = $upcomingEvent ? [$upcomingEvent] : [];
        } catch (Exception $exception) {
            return Helper::APIResponse(0, $exception->getMessage(), HTTP_FAILED, [
                'error' => $exception->getMessage()
            ]);
        }

        $data = compact('upcoming_events');
        return Helper::APIResponse(1, "Upcoming Events retrieved", HTTP_SUCCESS, $data);
    }


    //event participants API
    public function getParticipantsByEventID(Request $request)
    {
        $eventID = $request->input('event_id');
        $participants = Participant::where('event_id', $eventID)
            ->get()
            ->makeHidden([
                'registered', 'no_of_extra_bed', 'name', 'file_path', 'event_name', 'qr_code',
                'invoice_number', 'lunch_hotel', 'hotel_fees', 'cost_per_meal',
                'meals_total_cost', 'breakfast_fees', 'no_of_breakfast', 'total_hotel_extra_fees',
                'participation_fees', 'total_amount', 'amount_paid', 'receipt_number', 'data_paid',
                'dinner_hotel', 'created_at', 'updated_at'
            ]);

        return response()->json(['msg' => $participants]);

    }


    //attendance registration

    public function registerAttendance(Request $request)
    {
        // Validate input first
        $request->validate([
            'reference_code' => 'required|string',
            'registration_date_time' => 'required|date',
        ]);

        // Use provided datetime if available; otherwise use now
        $providedDateTime = $request->input('datetime');
        $currentTime = $providedDateTime ? Carbon::parse($providedDateTime) : Carbon::now();

        $referenceCode = $request->input('reference_code');

        // Get participant
        $participant = DB::table('event_participants')
            ->where('reference_code', $referenceCode)
            ->first();

        if (!$participant) {
            return Helper::APIResponse(0, 'Participant not found.', 404, []);
        }

        // Get all sessions (no restriction on session_description)
        $sessions = DB::table('upcoming_events')
            ->whereIn('session_description', ['Morning', 'Afternoon'])
            ->get();

        foreach ($sessions as $session) {
            $alreadyRegistered = DB::table('attendance_registration')
                ->where('session_id', $session->session_id)
                ->where('reference_code', $referenceCode)
                ->first();

            if ($alreadyRegistered) {
                $event = DB::table('events')
                    ->where('event_id', $session->event_id)
                    ->orWhere('id', $session->event_id)
                    ->first();

                return Helper::APIResponse(0, 'Participant is already registered for this session.', 409, [
                    'attendance' => $alreadyRegistered,
                    'participant_name' => $participant->participant,
                    'event_name' => $event->event_name ?? null,
                    'event_session' => $session->session_description,
                    'start_time' => $session->start_time,
                    'end_time' => $session->end_time,
                ]);
            }

            // Prepare insert data
            $insertData = [
                'session_id' => $session->session_id,
                'reference_code' => $referenceCode,
                'created_at' => $request->registration_date_time,
                'updated_at' => $currentTime,
            ];

            if (!empty($session->event_id)) {
                $insertData['event_id'] = $session->event_id;
            }

            // Insert attendance record
            $id = DB::table('attendance_registration')->insertGetId($insertData);

            $attendance = DB::table('attendance_registration')->where('id', $id)->first();

            $event = DB::table('events')
                ->where('event_id', $session->event_id)
                ->orWhere('id', $session->event_id)
                ->first();

            return Helper::APIResponse(1, 'Attendance registration successful.', 200, [
                'attendance' => $attendance,
                'participant_name' => $participant->participant,
                'event_name' => $event->event_name ?? null,
                'event_session' => $session->session_description,
                'start_time' => $session->start_time,
                'end_time' => $session->end_time,
            ]);
        }

        // If no sessions available
        return Helper::APIResponse(0, 'No session found.', 400, []);
    }

//    participant fees
    public function view_participant_fees(Request $id)
    {$event_id = $id->id;
        try {
            $fees = DB::table('event_prices')->where('event_id', $id->id)->get();

        } catch (Exception $exception) {
            return redirect()->back()->withInput()->withErrors(["exception" => "{$exception->getMessage()}"]);
        }

        return view("view_participant_fees", ['fees' => $fees, 'event_id' => $event_id]);
    }

    public function add_fees(Request $id)
    {
        $event_id = $id->id;
        $event_name = DB::table('event_prices')->where('event_id', ($id->id))->value('status');
        return view('add_fees.index', [ 'event_id' => $event_id]);
    }

    public function add_fees2(Request $request)
    {
        $event_id = $request->input('event_id');

        $data = [
            'event_id' => $event_id,
            'status' => $request->input('status'),
            'member_type' => $request->input('member_type'),
            'accommodation' => $request->boolean('accommodation'),
            'spouse_included' => $request->boolean('spouse_included'),
            'price' => $request->input('price'),
            'extra_person_price' => $request->input('extra_person_price', 600000),
        ];
        DB::table('event_prices')->insert($data);

        return redirect()->route('view_participant_fees', $event_id)
            ->with('message', 'Event fee added successfully');
    }

    public function edit_fees($id)
    {
        $data = DB::select('select * from event_prices where id = ?', [$id]);
        return view('edit_fees.index', ['data' => $data]);
    }


    public function update_fees(Request $request) {

        $id = $request->input('id');

        DB::table('event_prices')->where('id', $id)->update([
            'status' => $request->input('status'),
            'member_type' => $request->input('member_type'),
            'accommodation' => $request->boolean('accommodation'),
            'spouse_included' => $request->boolean('spouse_included'),
            'price' => $request->input('price'),
            'extra_person_price' => $request->input('extra_person_price', 600000),
        ]);
        return back()->with('message', 'Event fee updated successfully');
    }

    public function delete_fees($id) {
        DB::delete('delete from event_prices where id = ?',[$id]);
        return back()->with('message', 'Event price deleted Successfully');
    }

    public function viewHotelCapacity(Request $request)
    {
        $event_id = $request->id;
        $hotels = Hotel::where('event_id', $event_id)->get();
        $totalCapacity = $hotels->sum('quantity');
        $totalBooked = $hotels->sum('booked_count');
        $totalAvailable = $hotels->sum('available_count');

        return view("view_room_types.index", compact('hotels', 'event_id', 'totalCapacity', 'totalBooked', 'totalAvailable'));
    }

    public function updateHotelQuantity(Request $request, $id)
    {
        $hotel = Hotel::findOrFail($id);
        $request->validate(['quantity' => 'required|integer|min:0']);

        $diff = $request->quantity - $hotel->quantity;
        $hotel->quantity = $request->quantity;
        $hotel->available_count = max(0, $hotel->available_count + $diff);
        $hotel->save();

        return back()->with('success', 'Hotel capacity updated successfully.');
    }

}


