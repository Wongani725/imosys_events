<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EventPrices;
use App\Models\Event;
use App\Helpers\Helper;
use App\Models\BookingForm;
use App\Models\AttireSize;
use App\Models\Hotel;
use App\Models\Bookers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Mail\BookingInvoiceMail;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingApproved;
use App\Mail\ProofOfPaymentUpdated;
use App\Mail\BookingDeclined;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Mail\ReminderEmail;
use App\Mail\ProofOfPaymentEmail;
use App\Models\AttireColor;


class BookingController extends Controller
{

    public function getEventPrices()
    {
        $event = Event::orderBy('created_at', 'desc')->first();

        $eventId = $event ? $event->event_id : null;
//        dd($eventId);
        $event_prices = EventPrices::get();
//        dd($event_prices);

        return Helper::APIResponse(1, 'Successfully retrieved upcoming event status with prices.', HTTP_SUCCESS, [$event_prices]);

    }

    public function getQuestions(Request $request)
    {
        // Get latest event
        $event = Event::orderBy('created_at', 'desc')->first();

        if (!$event) {
            return Helper::APIResponse(0, 'No events found.', HTTP_NOT_FOUND, []);
        }

        // Pagination size (default 10)
        $perPage = $request->input('per_page', 10);

        // Paginate questions
        $questions = BookingForm::paginate($perPage);

        if ($questions->isEmpty()) {
            return Helper::APIResponse(1, 'No questions found.', HTTP_NOT_FOUND, []);
        }

        $questionsArray = [];

        foreach ($questions as $question) {
            $questionData = [
                'question' => $question->question,
                'type' => $question->type,
                'mandatory' => $question->priority,
            ];

            if ($question->type === 'dropdown') {
                switch ($question->question) {
                    case 'Attire Color':
                        $questionData['dropdown_values'] = AttireColor::pluck('color')->toArray();
                        break;

                    case 'Attire Size':
                        $questionData['dropdown_values'] = AttireSize::pluck('attire_size')->toArray();
                        break;

                    default:
                        $questionData['dropdown_values'] = [];
                        break;
                }
            }

            $questionsArray[] = $questionData;
        }

        // Return paginated data structure LIKE the participants example
        return Helper::APIResponse(1, 'Successfully retrieved questions.', HTTP_SUCCESS, [
            'questions' => $questionsArray,
            'pagination' => [
                'current_page' => $questions->currentPage(),
                'last_page'   => $questions->lastPage(),
                'per_page'    => $questions->perPage(),
                'total'       => $questions->total(),
            ]
        ]);
    }

    public function submitBooking(Request $request)
    {
        $user = $request->user();
        $email = $user->email;

        // Validate request
        try {
            $validated = $request->validate([
                'attire_color_id' => 'required|exists:attire_colors,id',
                'attire_size' => 'required|exists:attire_sizes,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return Helper::APIResponse(0, 'Validation failed.', 422, $e->errors());
        }

        // Prevent duplicate booking
        $existingBooking = Bookers::where('email', $email)
            ->whereIn('booking_status', ['Approved', 'Pending'])
            ->first();

        if ($existingBooking) {
            return Helper::APIResponse(0, 'You already made a booking.', 409, []);
        }


        // Generate booking ID
        $bookingID = strtoupper(uniqid('MLS-BK-'));

        try {
            // Create booking
            $booking = Bookers::create([
                'bookingID' => $bookingID,
                'event_id' => $request->event_id,
                'name' => $user->participant,
                'attire_color_id' => $request->attire_color_id,
                'attire_size' => $request->attire_size,
                'email' => $user->email_address,
                'phone_number' => $user->phone_number,
                'company' => $user->company_name,
                'position' => $user->position,
                'gender' => $user->gender,
                'booking_status' => 'Pending',
            ]);
            

            $colorName = AttireColor::find($request->attire_color_id)->color ?? 'Unknown';
            $sizeName  = AttireSize::find($request->attire_size)->attire_size ?? 'Unknown';

            $breakdown = [
                'bookingID'       => $bookingID,
                'name'            => $user->participant,
                'attire_color'    => $colorName,     
                'attire_size'     => $sizeName,     
                'booking_status'  => $booking->booking_status,
            ];

//            Mail::to($booking->email)->send(new BookingInvoiceMail($booking, $breakdown, []));

            return Helper::APIResponse(1, 'Booking successfully created!', 200, $breakdown);

        } catch (\Exception $e) {
            return Helper::APIResponse(0, 'Server error: ' . $e->getMessage(), 500, []);
        }
    }

    public function updateBooking(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'bookingID'     => 'required|string|exists:bookers,bookingID',
            'attire_color'  => 'required|string',
            'attire_size'   => 'required|string',
        ]);

        $booking = Bookers::where('bookingID', $request->bookingID)
            ->where('email', $user->email_address)
            ->where('booking_status', 'Pending')
            ->firstOrFail();

        $newColor = AttireColor::where('color', $request->attire_color)->firstOrFail();
        $newSize  = AttireSize::where('attire_size', $request->attire_size)->firstOrFail();

    
        if ($newColor->id != $booking->attire_color_id) {
            $booking->attire_color_id = $newColor->id;
        }

        $booking->attire_size = $newSize->id;

        $booking->save();

        return Helper::APIResponse(1, 'You have successfully updated your booking details!', 200, [
            'bookingID'      => $booking->bookingID,
            'attire_color'   => $newColor->color,
            'attire_size'    => $newSize->attire_size,
            'booking_status' => $booking->booking_status,
        ]);
    }
    public function updatePoP(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'bookingID' => 'required|string|exists:bookers,bookingID',
            'date_paid' => 'sometimes|date',
            'proof_of_payment' => 'sometimes|file|mimes:jpg,jpeg,png,pdf',
        ]);

        // Find the booking by bookingID
        $booking = Bookers::where('bookingID', $validated['bookingID'])->first();

        if (!$booking || $booking->memberID !== $user->reference_code) {
            return Helper::APIResponse(0, 'Booking not found or does not belong to the current user.', 404, []);
        }

        if (in_array($booking->booking_status, ['Cancelled', 'Declined'])) {
            return Helper::APIResponse(0, 'Cannot update a cancelled or declined booking.', 403, []);
        }



        $attachmentPath = null; 
        if ($request->hasFile('proof_of_payment')) {
            $file = $request->file('proof_of_payment');
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('proof_of_payment'), $fileName);
            $validated['proof_of_payment'] = 'proof_of_payment/' . $fileName;

            $attachmentPath = public_path('proof_of_payment/' . $fileName); // NEW
        }

        // Update booking details
        $booking->update([
            'date_paid' => $validated['date_paid'] ?? $booking->date_paid,
            'proof_of_payment' => $validated['proof_of_payment'] ?? $booking->proof_of_payment,
            'booking_status' => 'Payment Awaiting Receipting',
        ]);

        Mail::to($booking->email)->send(new ProofOfPaymentUpdated($booking));

        Mail::to('mbulla@mei.org.mw')->send(new ProofOfPaymentEmail($booking, $attachmentPath));

        return Helper::APIResponse(1, 'Proof of Payment successfully updated!', HTTP_SUCCESS, [
            'bookingID' => $booking->bookingID,
            'date_paid' => $booking->date_paid,
            'proof_of_payment' => $booking->proof_of_payment,
        ]);
    }

    public function cancelBooking(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'bookingID' => 'required|string|exists:bookers,bookingID',
        ]);

        $booking = Bookers::where('bookingID', $request->bookingID)
            ->where('email', $user->email_address)
            ->whereIn('booking_status', ['Pending Payment'])
            ->first();

        if (!$booking) {
            return Helper::APIResponse(0, 'Booking not found or cannot be cancelled.', 404);
        }

        $booking->booking_status = 'Cancelled';
        $booking->save();

        return Helper::APIResponse(1, 'Your booking has been cancelled.', 200, [
            'bookingID'      => $booking->bookingID,
            'booking_reference' => $booking->booking_reference,
            'new_status'     => 'Cancelled',
            'message'        => 'Booking cancelled.'
        ]);
    }

    public function getInvoiceLink(Request $request)
    {
        $user = $request->user();
        $referenceCode = $user->reference_code;

        $booking = Bookers::where('memberID', $referenceCode)->latest()->first();

        if (!$booking) {
            return response()->json(['error' => 'Booking not found.'], 404);
        }

        $event = Event::where('event_id', $booking->event_id)->first();
        $eventPrice = EventPrices::where('event_id', $booking->event_id)
            ->where('status', $booking->status == 1 ? 'Member' : 'Non-Member')
            ->first();

        $mealPrice = 100000;
        $invoiceItems = [];

        $breakdown = [
            'bookingID' => $booking->bookingID,
            'booking_status' => $booking->booking_status,
            'total_cost' => $booking->total_cost,
            'user_city' => $user->city,
            'user_address' => $user->address,
            'title' => $user->title,
            'event_name' => $event->event_name ?? '',
            'start_date' => $event->start_date ?? '',
            'end_date' => $event->end_date ?? '',
            'event_price' => $eventPrice->price ?? 0
        ];

        // Add event registration fee
        if ($eventPrice) {
            $invoiceItems[] = [
                'qty' => 1,
                'description' => 'Registration fees',
                'unit' => $eventPrice->price,
                'total' => $eventPrice->price
            ];
        }

        // Hotel
        $hotel = $booking->hotel;

        if ($hotel) {
            $invoiceItems[] = [
                'qty' => 1,
                'description' => 'Accommodation at ' . $hotel->name,
                'unit' => $hotel->extra_price ?? 0,
                'total' => $hotel->extra_price ?? 0
            ];
        }

        // Extra meals
        if (($booking->number_of_extra_meals ?? 0) > 0) {
            $invoiceItems[] = [
                'qty' => $booking->number_of_extra_meals,
                'description' => 'Extra Meal(s)',
                'unit' => $mealPrice,
                'total' => $mealPrice * $booking->number_of_extra_meals
            ];
        }

        // Generate PDF
        $pdf = Pdf::loadView('pdf.booking_invoice', [
            'booking' => $booking,
            'breakdown' => $breakdown,
            'invoiceItems' => $invoiceItems,
        ]);

        $fileName = 'invoices/booking_invoice_' . $booking->bookingID . '.pdf';
        Storage::disk('public')->put($fileName, $pdf->output());
        if (!Storage::disk('public')->exists($fileName)) {
            return response()->json(['error' => 'Failed to create invoice file.'], 500);
        }
//        dd(Storage::disk('public')->path($fileName));

        $link = Storage::disk('public')->url($fileName);

        return Helper::APIResponse(1, 'Invoice retrieved', 200, [$link]);
    }

   public function trackBookingStatus(Request $request)
    {
        $user = $request->user();

        $booking = Bookers::where('email', $user->email_address)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$booking) {
            return Helper::APIResponse(1, 'No booking found.', 200, []);
        }

        $colorName = $booking->attire_color_id
            ? AttireColor::find($booking->attire_color_id)?->color ?? 'N/A'
            : 'Not selected';

        $sizeName = $booking->attire_size
            ? AttireSize::find($booking->attire_size)?->attire_size ?? 'N/A'
            : 'Not selected';

        $event = Event::orderBy('created_at', 'desc')->first();

        $response = [
            'bookingID'       => $booking->bookingID,
            'name'            => $booking->name ?? $user->participant ?? 'N/A',
            'email'           => $booking->email,
            'phone_number'    => $booking->phone_number ?? 'N/A',
            'company'         => $booking->company ?? 'N/A',
            'attire_color'    => $colorName,
            'attire_size'     => $sizeName,
            'booking_status'  => $booking->booking_status,
            'event_name'      => $event?->event_name ?? 'Unknown Event',
            'event_start'     => $event?->start_date,
            'event_end'       => $event?->end_date,
            'event_theme'     => $event?->theme,
            'event_image'     => $event?->image ? url('storage/' . $event->image) : null,
            'created_at'      => $booking->created_at?->format('d M Y, H:i'),
        ];

        return Helper::APIResponse(1, 'Booking details retrieved successfully.', 200, $response);
    }


    public function trackBookingStatus1(Request $request)
    {
        $user = $request->user();
        $referenceCode = $user->reference_code;

        $eventId = $request->input('event_id');
        if (!$eventId) {
            return Helper::APIResponse(0, 'Event ID is required.', 400, []);
        }

        $booking = Bookers::with(['member', 'hotel', 'attireSize', 'statusInfo'])
            ->where('memberID', $referenceCode)
            ->where('event_id', $eventId)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$booking) {
            return Helper::APIResponse(1, 'No booking found for this user and event.', 200, []);
        }

        $event = Event::where('event_id', $eventId)->first();
        $statusLabel = $booking->statusInfo->status;

        $eventPrice = EventPrices::where('event_id', $booking->event_id)
            ->where('status', $statusLabel)
            ->value('price');

        $breakdown = [
            'bookingID'       => $booking->bookingID,
            'event_name'      => $event->event_name,
            'event_theme'     => $event->theme,
            'start_date'      => $event->start_date,
            'end_date'        => $event->end_date,
            'items'           => $invoiceItems,
            'total_cost'      => $totalCost,
            'booking_status'  => $booking->booking_status,
            'hotel'           => $booking->hotel->name ?? null,
            'member_type'     => $memberType,
            'accommodation'   => $booking->accommodation,
            'spouse_included' => $booking->spouse_included,
            'extras'          => $booking->extras,
            'room_price'      => 0,
        ];

        return Helper::APIResponse(1, 'Booking details retrieved.', 200, [$breakdown]);
    }

    public function getTerms(){
        $terms = DB::table('t&cs')->pluck('terms');
        return Helper::APIResponse(1, 'Terms and conditions retrieved', 200, $terms->toArray());
    }

  public function index(Request $request)
{
    $search = $request->input('search');
    $statusFilter = $request->input('status');

    $events = Event::query()
        ->where('event_status', 'active')
        ->orderByDesc('created_at')
        ->get();

    $selectedEventId =
        $request->input('event_id')
        ??
        optional($events->first())->event_id;

    $bookers = Bookers::query()
        ->with([
            'event',
            'attireSize',
            'hotel',
        ])
        ->when($statusFilter, function ($query) use ($statusFilter) {
            if ($statusFilter === 'all') return;
            $query->where('booking_status', $statusFilter);
        })
        ->when(!$statusFilter, function ($query) {
            $query->where('booking_status', '!=', 'Confirmed');
        })
        ->when($selectedEventId, function ($query) use ($selectedEventId) {
            $query->where('event_id', $selectedEventId);
        })
        ->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('bookingID', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        })
        ->latest()
        ->paginate(10)
        ->withQueryString();

    return view('bookers.index', compact(
        'bookers',
        'events',
        'selectedEventId',
        'statusFilter'
    ));
}




    /**
     * Mark payment as received and confirm booking
     */
public function confirmPayment($id)
{
    $booker = Bookers::findOrFail($id);

    if (in_array($booker->booking_status, ['Declined', 'Confirmed', 'Cancelled'])) {
        return back()->withErrors(['error' => 'This booking cannot be processed.']);
    }

    // Find all siblings sharing the same booking_reference
    $group = Bookers::where('booking_reference', $booker->booking_reference)
        ->whereIn('booking_status', ['Pending Payment', 'Approved', 'Pending'])
        ->get();

    if ($group->isEmpty()) {
        $group = collect([$booker]);
    }

    $hasGovWithAcc = $group->contains(fn($b) => $b->event->event_type === 'governance' && $b->accommodation);
    $hasMainWithAcc = $group->contains(fn($b) => $b->event->event_type === 'main' && $b->accommodation);
    $isBoth = $hasGovWithAcc && $hasMainWithAcc;

    $nameTagLink = null;

    DB::transaction(function () use ($group, $isBoth, &$nameTagLink) {

        $first = $group->first();
        $member = DB::table('members')
            ->where('member_id', $first->memberID)
            ->orWhere('email_address', $first->email)
            ->first();

        $memberId = $member->member_id ?? $first->memberID ?? 'BOOK-' . $first->bookingID;
        if (!$memberId) $memberId = 'BOOK-' . $first->bookingID;

        foreach ($group as $b) {
            /*
            |--------------------------------------------------------------------------
            | MEAL CALCULATION
            |--------------------------------------------------------------------------
            */

            $eventType = $b->event->event_type ?? 'main';
            $hasAccommodation = $b->accommodation;

            $totalMeals = \App\Helpers\MealCalculator::calculate($eventType, $hasAccommodation, $isBoth);

            if (!$nameTagLink) {
                $nameTagLink = route('show-participant', ['reference_code' => $memberId]);
            }

            /*
            |--------------------------------------------------------------------------
            | EVENT PARTICIPANT
            |--------------------------------------------------------------------------
            */

            $participantData = [
                'participant' => $b->name,
                'email_address' => $b->email,
                'phone_number' => $b->phone_number,
                'company_name' => $b->company,
                'status' => $b->member_type,
                'accommodation' => $b->accommodation,
                'hotel_id' => $b->accommodation ? $b->hotel_id : null,
                'event_selection' => $b->event_selection,
                'spouse_name' => $b->spouse_included ? 'Spouse' : null,
                'extras_count' => $b->extras ?? 0,
                'booker_id' => $b->bookingID,
                'meals' => $totalMeals,
                'updated_at' => now(),
                'created_at' => now(),
            ];

            DB::table('event_participants')->updateOrInsert(
                ['reference_code' => $memberId, 'event_id' => $b->event_id],
                $participantData
            );

            /*
            |--------------------------------------------------------------------------
            | MEAL COUPONS
            |--------------------------------------------------------------------------
            */

            $mealCoupons = [];

            $mealCoupons[] = [
                'participant_reference_code' => $memberId,
                'unique_code' => $memberId,
                'total_meals' => $totalMeals,
                'event_id' => $b->event_id,
                'status' => 'main',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($b->spouse_included) {
                $spouseCode = strtoupper($memberId . '-SPOUSE');

                $mealCoupons[] = [
                    'participant_reference_code' => $memberId,
                    'unique_code' => $spouseCode,
                    'total_meals' => $totalMeals,
                    'event_id' => $b->event_id,
                    'status' => 'spouse',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            for ($i = 1; $i <= ($b->extras ?? 0); $i++) {
                $extraCode = strtoupper($memberId . '-EXTRA-' . $i);

                $mealCoupons[] = [
                    'participant_reference_code' => $memberId,
                    'unique_code' => $extraCode,
                    'total_meals' => $totalMeals,
                    'event_id' => $b->event_id,
                    'status' => 'extra',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('meal_coupon')->upsert(
                $mealCoupons,
                ['unique_code', 'event_id'],
                ['total_meals', 'status', 'updated_at']
            );

            /*
            |--------------------------------------------------------------------------
            | UPDATE BOOKING
            |--------------------------------------------------------------------------
            */

            $b->booking_status = 'Confirmed';
            $b->reference_code = $memberId;
            $b->invoice_status = 'paid';
            $b->date_paid = now();
            $b->save();
        }
    });

    /*
    |--------------------------------------------------------------------------
    | EMAIL (OUTSIDE TRANSACTION) — ONE email with ONE name tag link
    |--------------------------------------------------------------------------
    */

    $eventNames = $group->pluck('event.event_name')->implode(' & ');
    try {
        Mail::to($booker->email)->send(
            new BookingApproved($booker, $nameTagLink, $eventNames)
        );
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('BookingApproved email failed: ' . $e->getMessage());
    }

    $confirmedCount = $group->count();
    $totalCredit = $group->sum('credit_applied');
    $totalDebt = $group->sum('debt_applied');
    $msg = "{$confirmedCount} booking(s) confirmed successfully.";
    if ($totalCredit > 0) $msg .= " Credit of MWK " . number_format($totalCredit, 2) . " applied.";
    if ($totalDebt > 0) $msg .= " Debt of MWK " . number_format($totalDebt, 2) . " applied.";
    return back()->with('success', $msg);
}

    /**
     * Calculate number of meals based on event selection and accommodation
     */
    private function calculateMeals($eventSelection, $accommodation)
    {
        if ($eventSelection === 'governance') {
            return $accommodation ? 3 : 2;
        }
        return $accommodation ? 5 : 2;
    }

    private function generateReferenceCode()
    {
        do {
            $code = 'IIA-' .
                strtoupper(Str::random(3)) . '-' .
                strtoupper(Str::random(3)) . '-' .
                rand(10, 99);
        } while (
            DB::table('event_participants')
                ->where('reference_code', $code)
                ->exists()
        );

        return $code;
    }


    public function getPrivacyPolicy(){
        $policy = DB::table('privacy_policy')->pluck('privacy_policy');
        return Helper::APIResponse(1, 'Privacy policy retrieved', 200, $policy->toArray());
    }

    public function decline(Request $request, $id)
    {
        $booker = Bookers::findOrFail($id);

        if (!in_array($booker->booking_status, ['Pending Payment'])) {
            return back()->withErrors(['error' => 'Only Pending Payment bookings can be declined.']);
        }

        $request->validate(['admin_note' => 'required|string|max:1000']);

        $group = Bookers::where('booking_reference', $booker->booking_reference)
            ->whereIn('booking_status', ['Pending Payment', 'Approved', 'Pending'])
            ->get();

        if ($group->isEmpty()) {
            $group = collect([$booker]);
        }

        DB::transaction(function () use ($group, $request) {
            foreach ($group as $b) {
                if ($b->accommodation && $b->hotel_id) {
                    $hotel = Hotel::find($b->hotel_id);
                    if ($hotel && $hotel->booked_count > 0) {
                        $hotel->available_count += 1;
                        $hotel->booked_count = max(0, $hotel->booked_count - 1);
                        $hotel->save();
                    }
                }

                $b->booking_status = 'Declined';
                $b->admin_note = $request->admin_note;
                $b->cancellation_reason = $request->admin_note;
                $b->save();
            }
        });

        // Restore credit and debt to member
        $member = \App\Models\Member::where('email_address', $booker->email)->first();
        if ($member) {
            $totalCredit = $group->sum('credit_applied');
            if ($totalCredit > 0) {
                $member->increment('credit', $totalCredit);
            }
            $totalDebt = $group->sum('debt_applied');
            if ($totalDebt > 0) {
                $member->increment('debt', $totalDebt);
            }
        }

        // Send decline email
        try {
            Mail::to($booker->email)->send(new BookingDeclined($booker));
        } catch (\Exception $e) {}

        // Create notification for member
        try {
            $member = \App\Models\Member::where('email_address', $booker->email)->first();
            if ($member) {
                $eventNames = $group->pluck('event.event_name')->implode(', ');
                $notification = \App\Models\Notification::create([
                    'title' => 'Booking Declined',
                    'message' => "Your booking for {$eventNames} has been declined. Reason: {$request->admin_note}",
                    'audience_type' => 'individual',
                    'created_by' => auth()->id(),
                ]);
                \App\Models\NotificationRecipient::create([
                    'notification_id' => $notification->id,
                    'member_id' => $member->id,
                ]);
            }
        } catch (\Exception $e) {}

        $count = $group->count();
        $totalCredit = $group->sum('credit_applied');
        $totalDebt = $group->sum('debt_applied');
        $msg = "{$count} booking(s) declined.";
        if ($totalCredit > 0) $msg .= " Credit of MWK " . number_format($totalCredit, 2) . " restored.";
        if ($totalDebt > 0) $msg .= " Debt of MWK " . number_format($totalDebt, 2) . " restored.";
        return back()->with('success', $msg . ' Email and notification sent.');
    }

    public function adminCancelBooking(Request $request, $id)
    {
        $booker = Bookers::findOrFail($id);

        if (!in_array($booker->booking_status, ['Pending Payment', 'Confirmed'])) {
            return back()->withErrors(['error' => 'Booking cannot be cancelled in its current status.']);
        }

        $request->validate(['admin_note' => 'required|string|max:1000']);

        $group = Bookers::where('booking_reference', $booker->booking_reference)
            ->whereIn('booking_status', ['Pending Payment', 'Confirmed', 'Approved', 'Pending'])
            ->get();

        if ($group->isEmpty()) {
            $group = collect([$booker]);
        }

        DB::transaction(function () use ($group, $request) {
            foreach ($group as $b) {
                if ($b->accommodation && $b->hotel_id) {
                    $hotel = Hotel::find($b->hotel_id);
                    if ($hotel && $hotel->booked_count > 0) {
                        $hotel->available_count += 1;
                        $hotel->booked_count = max(0, $hotel->booked_count - 1);
                        $hotel->save();
                    }
                }

                $b->booking_status = 'Cancelled';
                $b->admin_note = $request->admin_note;
                $b->cancellation_reason = $request->admin_note;
                $b->save();
            }
        });

        // Restore credit and debt to member
        $member = \App\Models\Member::where('email_address', $booker->email)->first();
        if ($member) {
            $totalCredit = $group->sum('credit_applied');
            if ($totalCredit > 0) {
                $member->increment('credit', $totalCredit);
            }
            $totalDebt = $group->sum('debt_applied');
            if ($totalDebt > 0) {
                $member->increment('debt', $totalDebt);
            }
        }

        try {
            Mail::to($booker->email)->send(new BookingDeclined($booker));
        } catch (\Exception $e) {}

        try {
            $member = \App\Models\Member::where('email_address', $booker->email)->first();
            if ($member) {
                $eventNames = $group->pluck('event.event_name')->implode(', ');
                $notification = \App\Models\Notification::create([
                    'title' => 'Booking Cancelled',
                    'message' => "Your booking for {$eventNames} has been cancelled. Reason: {$request->admin_note}",
                    'audience_type' => 'individual',
                    'created_by' => auth()->id(),
                ]);
                \App\Models\NotificationRecipient::create([
                    'notification_id' => $notification->id,
                    'member_id' => $member->id,
                ]);
            }
        } catch (\Exception $e) {}

        $count = $group->count();
        $totalCredit = $group->sum('credit_applied');
        $totalDebt = $group->sum('debt_applied');
        $msg = "{$count} booking(s) cancelled.";
        if ($totalCredit > 0) $msg .= " Credit of MWK " . number_format($totalCredit, 2) . " restored.";
        if ($totalDebt > 0) $msg .= " Debt of MWK " . number_format($totalDebt, 2) . " restored.";
        return back()->with('success', $msg . ' Email and notification sent.');
    }

    public function editBooking(Request $request, $id)
    {
        $booker = Bookers::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'total_cost' => 'nullable|numeric|min:0',
        ]);

        $costChanged = $request->total_cost && $request->total_cost != $booker->total_cost;

        $booker->name = $request->name;
        $booker->email = $request->email;
        $booker->phone_number = $request->phone_number;
        $booker->company = $request->company;
        if ($costChanged) {
            $booker->total_cost = $request->total_cost;
            $adjustedTotal = $request->total_cost - (float)($booker->credit_applied ?? 0) + (float)($booker->debt_applied ?? 0);
            $booker->balance = max(0, $adjustedTotal - ($booker->amount_paid ?? 0));
        }
        $booker->booking_status = 'Pending Payment';
        $booker->invoice_status = 'pending';
        $booker->save();

        // Generate new invoice
        $invoiceNumber = 'INV-' . strtoupper(uniqid());
        \App\Models\BookingInvoice::create([
            'booking_id' => $booker->bookingID,
            'invoice_number' => $invoiceNumber,
            'amount' => $booker->total_cost,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
        $booker->invoice_status = 'sent';
        $booker->save();

        // Send invoice email with correct price row
        try {
            $priceHotelCode = null;
            if ($booker->accommodation && $booker->hotel_id) {
                $hotel = Hotel::find($booker->hotel_id);
                if ($hotel) {
                    $name = strtolower($hotel->name);
                    $priceHotelCode = str_contains($name, 'nkopola') ? 'nkopola' : 'sun_n_sand';
                }
            }

            $priceRow = \App\Models\EventPrices::where('event_id', $booker->event_id)
                ->where('member_type', $booker->member_type ?? 'Member')
                ->where('accommodation', $booker->accommodation)
                ->when($booker->accommodation && $priceHotelCode, function ($q) use ($priceHotelCode, $booker) {
                    return $q->where('hotel', $priceHotelCode)
                            ->where('spouse_included', $booker->spouse_included);
                })
                ->first();

            $extras = $booker->extras ?? 0;
            $total = $booker->total_cost;
            Mail::to($booker->email)->send(new \App\Mail\BookingInvoiceMail($booker, $priceRow, $extras, $total));
        } catch (\Exception $e) {}

        return back()->with('success', 'Booking updated. New invoice sent to ' . $booker->email);
    }

    public function enterPayment(Request $request, $id)
    {
        $booker = Bookers::findOrFail($id);

        $request->validate([
            'amount_paid' => 'required|numeric|min:0',
        ]);

        $amountPaid = (float) $request->amount_paid;
        $adjustedTotal = (float) $booker->total_cost - (float)($booker->credit_applied ?? 0) + (float)($booker->debt_applied ?? 0);
        $newBalance = max(0, $adjustedTotal - $amountPaid);

        $booker->amount_paid = $amountPaid;
        $booker->balance = $newBalance;
        $booker->receipt_number = $request->receipt_number ?? $booker->receipt_number;
        $booker->save();

        return back()->with('success', 'Payment recorded. Balance: MWK ' . number_format($newBalance, 2));
    }

    /**
     * Allocate room to a booking
     */
    public function allocateRoom(Request $request, $id)
    {
        $booker = Bookers::findOrFail($id);
        
        if (!$booker->accommodation) {
            return back()->withErrors(['error' => 'This booking does not include accommodation.']);
        }

        if (!$booker->hotel_id) {
            return back()->withErrors(['error' => 'No hotel assigned to this booking.']);
        }

        $request->validate([
            'room_number' => 'required|string|max:100',
        ]);

        $hotel = Hotel::findOrFail($booker->hotel_id);
        
        if ($hotel->available_count <= 0) {
            return back()->withErrors(['error' => 'No rooms available at this hotel.']);
        }

        DB::transaction(function () use ($booker, $hotel, $request) {
            $hotel->available_count -= 1;
            $hotel->booked_count += 1;
            $hotel->save();

            $booker->room_number = $request->room_number;
            $booker->save();
        });

        return back()->with('success', 'Room allocated successfully.');
    }

    public function sendReminderEmails()
    {
        $pendingBookers = Bookers::where('booking_status', 'Pending')->get();

        if ($pendingBookers->isEmpty()) {
            return redirect()->back()->with('success', 'No pending bookings found.');
        }

        foreach ($pendingBookers as $booker) {
            Mail::to($booker->email)->send(new ReminderEmail($booker));
        }

        return redirect()->back()->with('success', 'Reminder emails sent to all pending bookers.');
    }

    public function adminViewPoP($bookingID)
    {
        $booking = Bookers::where('bookingID', $bookingID)->first();

        if (!$booking || !$booking->proof_of_payment) {
            abort(404);
        }

        $path = Storage::disk('public')->path($booking->proof_of_payment);
        if (!file_exists($path)) {
            abort(404);
        }

        $mime = mime_content_type($path) ?: 'application/octet-stream';
        $content = file_get_contents($path);

        return response($content, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    }
}