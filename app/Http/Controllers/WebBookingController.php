<?php

namespace App\Http\Controllers;

use App\Models\AttireSize;
use App\Models\Bookers;
use App\Models\BookingInvoice;
use App\Models\Event;
use App\Models\EventPrices;
use App\Models\Hotel;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\BookingInvoiceMail;
use App\Mail\ConsolidatedInvoiceMail;
use App\Mail\BookingApproved;
use App\Mail\ProofOfPaymentEmail;
use Barryvdh\DomPDF\Facade\Pdf;


class WebBookingController extends Controller
{
    /**
     * Calculate total cost based on selections
     */
    private function calculateTotalCost($eventId, $memberStatus, $eventSelection, $accommodation, $hotel, $spouseIncluded, $extras)
    {
        $total = 0;

        $eventTypes = [$eventSelection];
        
        foreach ($eventTypes as $eventType) {
            $price = EventPrices::where('event_id', $eventId)
                ->where('member_type', $memberStatus)
                ->where('accommodation', $accommodation)
                ->where('spouse_included', $spouseIncluded)
                ->where('event_type', $eventType)
                ->when($accommodation, function ($query) use ($hotel) {
                    return $query->where('hotel', $hotel);
                })
                ->first();
            
            if ($price) {
                $total += $price->price;
            }
        }
        
        // Add extras cost
        if ($extras > 0) {
            $extraPrice = EventPrices::where('event_id', $eventId)
                ->where('event_type', $eventTypes[0] ?? 'governance')
                ->value('extra_person_price') ?? 600000;
            $total += ($extras * $extraPrice);
        }
        
        return $total;
    }

    public function index(Request $request){
        $eventId = $request->query('event_id');
        $event = $eventId ? \App\Models\Event::where('event_id', $eventId)->first() : null;
        $terms = $eventId ? DB::table('terms')->where('event_id', $eventId)->first() : null;
        return view('terms.index', compact('terms', 'event'));
    }

    public function memberTerms($eventId)
    {
        $event = \App\Models\Event::where('event_id', $eventId)->first();
        $terms = DB::table('terms')->where('event_id', $eventId)->value('terms');
        return view('web_booking.terms', compact('terms', 'event', 'eventId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'event_id' => 'required|exists:events,event_id',
        ]);
        Terms::create(['terms' => $request->content, 'event_id' => $request->event_id]);
        return redirect()->route('get-terms', ['event_id' => $request->event_id])->with('success', 'Terms added.');
    }

    public function update(Request $request, $id)
    {
        $request->validate(['content' => 'required|string']);
        $terms = Terms::findOrFail($id);
        $terms->update(['terms' => $request->content]);
        return redirect()->route('get-terms', ['event_id' => $terms->event_id])->with('success', 'Terms updated.');
    }


    public function submitBooking(Request $request)
    {
        $user = auth()->guard('member')->user();

        if (!$user) {
            return redirect()->route('participant.login');
        }

        $eventIds = $request->input('event_ids', []);
        if (empty($eventIds)) {
            return back()->withErrors(['error' => 'Select at least one event.']);
        }

        $memberType = ($user->status === 'Member') ? 'Member' : 'Non-Member';

        /* -----------------------------------
        | Validate each event's selections
        -----------------------------------*/
        $request->validate([
            'event_ids' => 'required|array|min:1',
            'event_ids.*' => 'required|exists:events,event_id',
            'accommodation' => 'required|array',
            'accommodation.*' => 'required|in:0,1',
            'hotel' => 'nullable|array',
            'hotel.*' => 'nullable|integer|exists:hotel,id',
            'spouse_included' => 'nullable|array',
            'spouse_included.*' => 'nullable|in:0,1',
            'extras_count' => 'nullable|array',
            'extras_count.*' => 'nullable|integer|min:0',
            'attire_size_id' => 'nullable|exists:attire_sizes,id',
        ]);

        /* -----------------------------------
        | Prepare data
        -----------------------------------*/
        $bookingRef = 'IIA-BK-' . strtoupper(uniqid());

        // Auto-group with existing pending bookings for different events
        $existingRef = Bookers::where('email', $user->email_address)
            ->whereIn('booking_status', ['Pending Payment', 'Approved', 'Pending'])
            ->whereNotIn('event_id', $eventIds)
            ->value('booking_reference');
        if ($existingRef) {
            $bookingRef = $existingRef;
        }

        $memberRecord = \App\Models\Member::where('email_address', $user->email_address)->first();

        $bookings = [];
        $totalCombinedCost = 0;
        $allPriceRows = [];

        DB::beginTransaction();

        try {

            foreach ($eventIds as $i => $eventId) {

                $event = \App\Models\Event::where('event_id', $eventId)->first();
                if (!$event) continue;

                /* -----------------------------------
                | Skip duplicate
                -----------------------------------*/
                $existing = Bookers::where('email', $user->email_address)
                    ->where('event_id', $eventId)
                    ->whereIn('booking_status', ['Pending Payment', 'Confirmed'])
                    ->first();

                if ($existing) continue;

                /* -----------------------------------
                | Per-event selections
                -----------------------------------*/
                $accommodation = (int)($request->accommodation[$i] ?? 0);
                $hotelId = $accommodation ? (int)($request->hotel[$i] ?? 0) : null;
                $spouse = $accommodation ? (int)($request->spouse_included[$i] ?? 0) : 0;
                $extras = $accommodation ? (int)($request->extras_count[$i] ?? 0) : 0;

                /* -----------------------------------
                | Hotel & pricing
                -----------------------------------*/
                $hotelRecord = null;
                $priceHotelCode = null;
                if ($accommodation && $hotelId) {
                    $hotelRecord = Hotel::find($hotelId);
                    if ($hotelRecord) {
                        $name = strtolower($hotelRecord->name);
                        $priceHotelCode = str_contains($name, 'nkopola') ? 'nkopola' : 'sun_n_sand';
                    }
                }

                $priceRow = EventPrices::where('event_id', $eventId)
                    ->where('member_type', $memberType)
                    ->where('accommodation', $accommodation)
                    ->when($accommodation, function ($q) use ($priceHotelCode, $spouse) {
                        return $q->where('hotel', $priceHotelCode)
                                ->where('spouse_included', $spouse);
                    })
                    ->first();

                if (!$priceRow) {
                    DB::rollBack();
                    return back()->withErrors(['error' => "No pricing for {$event->event_name}."]);
                }

                $totalCost = $priceRow->price + ($extras * $priceRow->extra_person_price);
                $totalCombinedCost += $totalCost;
                $allPriceRows[] = $priceRow;

                /* -----------------------------------
                | Create Bookers record
                -----------------------------------*/
                $booking = Bookers::create([
                    'booking_reference' => $bookingRef,
                    'event_id' => $eventId,
                    'event_selection' => $event->event_type ?? 'main',
                    'accommodation' => $accommodation,
                    'hotel_id' => $hotelRecord ? $hotelRecord->id : null,
                    'spouse_included' => $spouse,
                    'extras' => $extras,
                    'attire_size_id' => $request->attire_size_id,
                    'name' => $user->participant,
                    'email' => $user->email_address,
                    'phone_number' => $user->phone_number,
                    'company' => $user->company_name,
                    'member_type' => $memberType,
                    'memberID' => $user->member_id,
                    'booking_status' => 'Pending Payment',
                    'invoice_status' => 'pending',
                    'total_cost' => $totalCost,
                    'balance' => $totalCost,
                ]);

                /* -----------------------------------
                | Create invoice
                -----------------------------------*/
                $invoiceNumber = 'INV-' . strtoupper(uniqid());
                BookingInvoice::create([
                    'booking_id' => $booking->bookingID,
                    'invoice_number' => $invoiceNumber,
                    'amount' => $totalCost,
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);

                $booking->invoice_status = 'sent';
                $booking->save();

                /* -----------------------------------
                | Update hotel availability
                -----------------------------------*/
                if ($booking->accommodation && $booking->hotel_id) {
                    Hotel::where('id', $booking->hotel_id)->decrement('available_count');
                    Hotel::where('id', $booking->hotel_id)->increment('booked_count');
                }

                $bookings[] = $booking;
            }

            if (empty($bookings)) {
                DB::rollBack();
                return back()->withErrors(['error' => 'All selected events are already booked or unavailable.']);
            }

            /* -----------------------------------
            | Apply credit / debt proportionally
            -----------------------------------*/
            if ($memberRecord) {
                $memberCredit = (float) $memberRecord->credit;
                $memberDebt = (float) $memberRecord->debt;
                $totalAppliedCredit = 0;
                $totalAppliedDebt = 0;

                foreach ($bookings as $booking) {
                    $share = $booking->total_cost / $totalCombinedCost;
                    $creditShare = $memberCredit > 0 ? round($memberCredit * $share, 2) : 0;
                    $debtShare = $memberDebt > 0 ? round($memberDebt * $share, 2) : 0;

                    $booking->credit_applied = $creditShare;
                    $booking->debt_applied = $debtShare;
                    $booking->balance = max(0, $booking->total_cost - $creditShare + $debtShare);
                    $booking->save();

                    $totalAppliedCredit += $creditShare;
                    $totalAppliedDebt += $debtShare;
                }

                if ($memberCredit > 0) {
                    $memberRecord->decrement('credit', $totalAppliedCredit);
                }
                if ($totalAppliedDebt > 0) {
                    $memberRecord->decrement('debt', $totalAppliedDebt);
                }
            }

            DB::commit();

            /* -----------------------------------
            | Send invoice email
            -----------------------------------*/
            try {
                if (count($bookings) === 1) {
                    Mail::to($user->email_address)->send(
                        new BookingInvoiceMail($bookings[0], $allPriceRows[0], $bookings[0]->extras, $bookings[0]->total_cost)
                    );
                } else {
                    Mail::to($user->email_address)->send(
                        new ConsolidatedInvoiceMail($bookings, $allPriceRows)
                    );
                }
            } catch (\Exception $mailEx) {
                \Illuminate\Support\Facades\Log::error('Invoice email failed: ' . $mailEx->getMessage());
            }

            $eventNames = collect($bookings)->pluck('event.event_name')->implode(' & ');
            return redirect()->back()->with('success', "Booking submitted for {$eventNames}! Check your email for the invoice.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Server error: ' . $e->getMessage()]);
        }
    }

    public function getBookingView($event_id)
    {
        $user = auth()->guard('member')->user();

        if (!$user) {
            return redirect()->route('participant.login');
        }

        $event = Event::where('event_id', $event_id)->firstOrFail();

        $booking = Bookers::where('email', $user->email_address)
            ->where('event_id', $event_id)
            ->whereNotIn('booking_status', ['Cancelled', 'Declined'])
            ->latest()
            ->first();

             $hotels = Hotel::query()
            ->where('event_id', $event_id)
            ->orderBy('name')

            ->get();

            $eventPrices = EventPrices::query()

            ->where('event_id', $event_id)

            ->get();

              $priceMap = $eventPrices->map(function ($price) {

            return [

                'event_id' =>
                    $price->event_id,

                'member_type' =>
                    $price->member_type,

                'accommodation' =>
                    $price->accommodation,

                'hotel' =>
                    $price->hotel,

                'spouse_included' =>
                    $price->spouse_included,

                'price' =>
                    $price->price,

                'extra_person_price' =>
                    $price->extra_person_price,
            ];

        });


            $terms = DB::table('terms')->where('event_id', $event_id)->value('terms')
                ?? DB::table('terms')->value('terms');

            $attireSizes = \App\Models\AttireSize::where('event_id', $event_id)->orderBy('id')->get();

            $memberType = ($user->status === 'Member') ? 'Member' : 'Non-Member';

        return view('web_booking.view_booking', compact(
            'user',
            'event',
            'booking',
            'terms',
            'priceMap',
            'hotels',
            'eventPrices',
            'attireSizes',
            'memberType'
        ));
    }


    public function getInvoicePDF(string $bookingID)
    {
        $user = auth()->guard('member')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }


        $booking = Bookers::where('bookingID', $bookingID)
        ->where('email', $user->email_address)
        ->first();


        if (!$booking) {
            return response()->json(['error' => 'Booking not found.'], 404);
        }

        $event = Event::where('event_id', $booking->event_id)->first();

        $memberType = $user->status === 'Member' ? 'Member' : 'Non-Member';

        /*
        |--------------------------------------------------------------------------
        | GET BASE PRICE ROW (MATCH EXACT BOOKING CONFIGURATION)
        |--------------------------------------------------------------------------
        */
        $priceHotelCode = null;
        if ($booking->accommodation && $booking->hotel_id) {
            $hotel = Hotel::find($booking->hotel_id);
            if ($hotel) {
                $name = strtolower($hotel->name);
                $priceHotelCode = str_contains($name, 'nkopola') ? 'nkopola' : 'sun_n_sand';
            }
        }

        $priceRow = EventPrices::where('event_id', $booking->event_id)
            ->where('member_type', $memberType)
            ->where('accommodation', $booking->accommodation)
            ->where('spouse_included', $booking->spouse_included)
            ->when($priceHotelCode, function ($q) use ($priceHotelCode) {
                return $q->where('hotel', $priceHotelCode);
            })
            ->first();

        if (!$priceRow) {
            return back()->withErrors([
                'error' => 'Pricing configuration not found for this booking.'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | BUILD INVOICE ITEMS (DYNAMIC BREAKDOWN)
        |--------------------------------------------------------------------------
        */
        $items = [];

        // 1. Base registration
        $items[] = [
            'description' => $priceRow->status,
            'qty' => 1,
            'price' => $priceRow->price,
            'total' => $priceRow->price,
        ];

        // 2. Extras
        $extraTotal = 0;

        if ($booking->extras > 0) {
            $extraTotal = $booking->extras * $priceRow->extra_person_price;
            $items[] = [
                'description' => 'Extra Guest(s) above 2 years',
                'qty' => $booking->extras,
                'price' => $priceRow->extra_person_price,
                'total' => $extraTotal,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | TOTAL
        |--------------------------------------------------------------------------
        */
        $totalCost = collect($items)->sum('total');

        /*
        |--------------------------------------------------------------------------
        | BREAKDOWN OBJECT
        |--------------------------------------------------------------------------
        */
        $breakdown = [
            'bookingID' => $booking->bookingID,
            'event_name' => $event->event_name,
            'event_theme' => $event->theme,
            'start_date' => $event->start_date,
            'end_date' => $event->end_date,
            'booking_status' => $booking->booking_status,
            'items' => $items,
            'total_cost' => $totalCost,
        ];

        $invoice = \App\Models\BookingInvoice::where('booking_id', $booking->bookingID)->first();
        $invoiceItems = $items;
        $total = $totalCost;

        $pdf = PDF::loadView('pdf.booking_invoice', compact('booking', 'invoiceItems', 'total', 'invoice'));

        return $pdf->stream('invoice-' . ($booking->booking_reference ?? $booking->bookingID) . '.pdf');
    }

    public function getConsolidatedInvoicePDF(string $bookingID)
    {
        $user = auth()->guard('member')->user();
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);

        $booking = Bookers::where('bookingID', $bookingID)
            ->where('email', $user->email_address)
            ->first();

        if (!$booking) return response()->json(['error' => 'Booking not found.'], 404);

        $siblings = Bookers::where('booking_reference', $booking->booking_reference)
            ->where('bookingID', '!=', $booking->bookingID)
            ->get();

        $allBookings = $siblings->isNotEmpty() ? $siblings->prepend($booking) : collect([$booking]);

        $items = [];
        foreach ($allBookings as $b) {
            $event = Event::where('event_id', $b->event_id)->first();
            $memberType = $user->status === 'Member' ? 'Member' : 'Non-Member';

            $priceHotelCode = null;
            if ($b->accommodation && $b->hotel_id) {
                $hotel = Hotel::find($b->hotel_id);
                if ($hotel) {
                    $name = strtolower($hotel->name);
                    $priceHotelCode = str_contains($name, 'nkopola') ? 'nkopola' : 'sun_n_sand';
                }
            }

            $priceRow = EventPrices::where('event_id', $b->event_id)
                ->where('member_type', $memberType)
                ->where('accommodation', $b->accommodation)
                ->where('spouse_included', $b->spouse_included)
                ->when($priceHotelCode, fn($q) => $q->where('hotel', $priceHotelCode))
                ->first();

            $eventItems = [];
            $eventItems[] = [
                'description' => $priceRow->status ?? 'Registration',
                'qty' => 1,
                'price' => $priceRow->price ?? $b->total_cost,
                'total' => $priceRow->price ?? $b->total_cost,
                'event_name' => $event->event_name ?? $b->event_id,
            ];

            if (($b->extras ?? 0) > 0 && $priceRow) {
                $eventItems[] = [
                    'description' => 'Additional Participants',
                    'qty' => $b->extras,
                    'price' => $priceRow->extra_person_price,
                    'total' => $b->extras * $priceRow->extra_person_price,
                    'event_name' => '',
                ];
            }

            $items[] = [
                'event' => $event->event_name ?? $b->event_id,
                'items' => $eventItems,
                'subtotal' => collect($eventItems)->sum('total'),
                'credit' => $b->credit_applied ?? 0,
                'debt' => $b->debt_applied ?? 0,
                'balance' => $b->balance ?? collect($eventItems)->sum('total'),
            ];
        }

        $pdf = PDF::loadView('pdf.consolidated_invoice', [
            'bookings' => $allBookings,
            'items' => $items,
            'grandTotal' => collect($items)->sum('subtotal'),
        ]);

        return $pdf->stream('invoice-' . ($booking->booking_reference ?? $booking->bookingID) . '.pdf');
    }

public function updatePoP(Request $request, string $bookingID)
{
    $user = auth()->guard('member')->user();

    if (!$user) {
        return redirect()->route('participant.login');
    }

    $request->validate([
        'proof_of_payment' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
    ]);

    $booking = Bookers::where('bookingID', $bookingID)
        ->where('email', $user->email_address)
        ->whereIn('booking_status', ['Pending', 'Pending Payment', 'Approved'])
        ->first();

    if (!$booking) {
        return back()->withErrors(['error' => 'Booking not found.']);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE OLD FILES
    |--------------------------------------------------------------------------
    */

    $siblings = Bookers::where('booking_reference', $booking->booking_reference)->get();

    foreach ($siblings as $b) {
        if ($b->proof_of_payment && Storage::disk('public')->exists($b->proof_of_payment)) {
            Storage::disk('public')->delete($b->proof_of_payment);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | STORE NEW FILE
    |--------------------------------------------------------------------------
    */

    $path = $request->file('proof_of_payment')
        ->store('proof_of_payments', 'public');

    /*
    |--------------------------------------------------------------------------
    | UPDATE ALL SIBLINGS
    |--------------------------------------------------------------------------
    */

    foreach ($siblings as $b) {
        $b->update([
            'proof_of_payment' => $path,
            'booking_status' => 'Pending Payment',
        ]);
    }

    // Send confirmation email
    try {
        $fullPath = Storage::disk('public')->path($path);
        Mail::to($booking->email)->send(new ProofOfPaymentEmail($booking, $fullPath));
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('PoP email failed: ' . $e->getMessage());
    }

    return back()->with(
        'success',
        'Proof of payment uploaded successfully. We will review and confirm your booking shortly.'
    );
}

public function viewPoP($bookingID)
{
    $user = auth()->guard('member')->user();
    if (!$user) abort(401);

    $booking = Bookers::where('bookingID', $bookingID)
        ->where('email', $user->email_address)
        ->first();

    if (!$booking || !$booking->proof_of_payment) abort(404);

    $path = Storage::disk('public')->path($booking->proof_of_payment);
    if (!file_exists($path)) abort(404);

    $mime = mime_content_type($path) ?: 'application/octet-stream';
    $content = file_get_contents($path);

    return \Illuminate\Support\Facades\Response::make($content, 200, [
        'Content-Type' => $mime,
        'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
    ]);
}

    public function updateBooking(Request $request)
    {
        $user = auth()->guard('member')->user();
        if (!$user) return redirect()->route('participant.login');

        $booking = Bookers::where('bookingID', $request->bookingID)->firstOrFail();
        $event = Event::where('event_id', $booking->event_id)->first();
        if (!$event) return back()->withErrors(['error' => 'Event not found.']);

        /* -----------------------------------
        | Validation
        -----------------------------------*/
        $rules = [
            'hotel' => 'nullable|integer|exists:hotel,id',
            'accommodation' => 'required|in:0,1',
            'spouse_included' => 'required_if:accommodation,1|in:0,1',
            'extras' => 'nullable|integer|min:0',
            'attire_size_id' => 'nullable|exists:attire_sizes,id',
        ];
        $validated = $request->validate($rules);

        $accommodation = (int)$request->accommodation;
        $newHotelId = $accommodation ? (int)$request->hotel : null;
        $spouse = $accommodation ? (int)$request->spouse_included : 0;
        $extras = $accommodation ? (int)$request->extras : 0;
        $memberStatus = $user->status === 'Member' ? 'Member' : 'Non-Member';

        /* -----------------------------------
        | Handle hotel availability counts
        -----------------------------------*/
        $oldHotelId = $booking->hotel_id;

        // If changing hotels: increment old hotel, decrement new hotel
        if ($oldHotelId && $newHotelId && $oldHotelId != $newHotelId) {
            Hotel::where('id', $oldHotelId)->increment('available_count');
            Hotel::where('id', $oldHotelId)->decrement('booked_count');
            Hotel::where('id', $newHotelId)->decrement('available_count');
            Hotel::where('id', $newHotelId)->increment('booked_count');
        }

        // If removing accommodation: increment old hotel
        if ($oldHotelId && !$newHotelId) {
            Hotel::where('id', $oldHotelId)->increment('available_count');
            Hotel::where('id', $oldHotelId)->decrement('booked_count');
        }

        // If adding accommodation: decrement new hotel
        if (!$oldHotelId && $newHotelId) {
            Hotel::where('id', $newHotelId)->decrement('available_count');
            Hotel::where('id', $newHotelId)->increment('booked_count');
        }

        /* -----------------------------------
        | Derive price code from hotel name
        -----------------------------------*/
        $priceHotelCode = null;
        if ($newHotelId) {
            $hotelRecord = Hotel::find($newHotelId);
            if ($hotelRecord) {
                $name = strtolower($hotelRecord->name);
                $priceHotelCode = str_contains($name, 'nkopola') ? 'nkopola' : 'sun_n_sand';
            }
        }

        /* -----------------------------------
        | Look up price
        -----------------------------------*/
        $priceRow = EventPrices::where('event_id', $booking->event_id)
            ->where('member_type', $memberStatus)
            ->where('accommodation', $accommodation)
            ->when($accommodation == 1, function ($q) use ($priceHotelCode, $spouse) {
                return $q->where('hotel', $priceHotelCode)->where('spouse_included', $spouse);
            })
            ->first();

        if (!$priceRow) {
            return back()->withErrors(['error' => 'No pricing found for this selection.']);
        }

        $totalCost = $priceRow->price + ($extras * $priceRow->extra_person_price);

        /* -----------------------------------
        | Update booking
        -----------------------------------*/
        $booking->accommodation = $accommodation;
        $booking->hotel_id = $newHotelId;
        $booking->spouse_included = $spouse;
        $booking->extras = $extras;
        $booking->attire_size_id = $request->attire_size_id ?? null;
        $adjustedBalance = $totalCost - (float)($booking->credit_applied ?? 0) + (float)($booking->debt_applied ?? 0);
        $booking->total_cost = $totalCost;
        $booking->balance = max(0, $adjustedBalance);
        $booking->save();

        // Re-generate invoice
        $invoice = BookingInvoice::updateOrCreate(
            ['booking_id' => $booking->bookingID],
            ['amount' => $totalCost, 'status' => 'sent', 'sent_at' => now()]
        );

        // Send updated invoice — consolidated if sibling exists
        try {
            $siblings = Bookers::where('booking_reference', $booking->booking_reference)
                ->where('bookingID', '!=', $booking->bookingID)
                ->get();

            if ($siblings->isNotEmpty()) {
                $all = $siblings->prepend($booking);
                $priceRows = [];
                foreach ($all as $b) {
                    $pr = \App\Models\EventPrices::where('event_id', $b->event_id)
                        ->where('member_type', $b->member_type ?? 'Member')
                        ->where('accommodation', $b->accommodation)
                        ->first();
                    $priceRows[] = $pr;
                }
                Mail::to($booking->email)->send(new ConsolidatedInvoiceMail($all, $priceRows));
            } else {
                Mail::to($booking->email)->send(new BookingInvoiceMail($booking, $priceRow, $extras, $totalCost));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Update invoice email failed: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Booking updated. An updated invoice has been sent to your email.');
    }

    public function cancelBookingWeb(Request $request)
    {
        $user = auth()->guard('member')->user();
        if (!$user) return redirect()->route('participant.login');

        $request->validate(['booking_id' => 'required|exists:bookers,bookingID']);

        $booking = Bookers::where('bookingID', $request->booking_id)
            ->where('email', $user->email_address)
            ->whereIn('booking_status', ['Pending Payment'])
            ->first();

        if (!$booking) {
            return back()->withErrors(['error' => 'Booking not found or cannot be cancelled.']);
        }

        // Cancel all siblings with same booking_reference
        $siblings = Bookers::where('booking_reference', $booking->booking_reference)->get();

        foreach ($siblings as $b) {
            if ($b->accommodation && $b->hotel_id) {
                Hotel::where('id', $b->hotel_id)->increment('available_count');
                Hotel::where('id', $b->hotel_id)->decrement('booked_count');
            }

            $b->booking_status = 'Cancelled';
            $b->cancellation_reason = 'Cancelled by participant';
            $b->save();
        }

        // Restore credit and debt to member
        $member = \App\Models\Member::where('email_address', $user->email_address)->first();
        if ($member) {
            $totalCredit = $siblings->sum('credit_applied');
            if ($totalCredit > 0) {
                $member->increment('credit', $totalCredit);
            }
            $totalDebt = $siblings->sum('debt_applied');
            if ($totalDebt > 0) {
                $member->increment('debt', $totalDebt);
            }
        }

        return redirect()->route('member-dashboard')->with('status', 'Booking cancelled successfully.');
    }

    public function restoreBooking(Request $request)
    {
        $user = auth()->guard('member')->user();
        if (!$user) return redirect()->route('participant.login');

        $request->validate(['booking_id' => 'required|exists:bookers,bookingID']);

        $booking = Bookers::where('bookingID', $request->booking_id)
            ->where('email', $user->email_address)
            ->whereIn('booking_status', ['Cancelled', 'Declined'])
            ->first();

        if (!$booking) {
            return back()->withErrors(['error' => 'Booking not found or cannot be restored.']);
        }

        // If booking had accommodation, check hotel availability
        $removeAccommodation = $request->remove_accommodation ? true : false;

        if ($booking->accommodation && $booking->hotel_id && !$removeAccommodation) {
            $hotel = Hotel::find($booking->hotel_id);
            if ($hotel && $hotel->available_count <= 0) {
                return back()->withErrors([
                    'error' => 'This hotel is now fully booked. You can restore without accommodation (basic rate) or cancel.'
                ]);
            }
            Hotel::where('id', $booking->hotel_id)->decrement('available_count');
            Hotel::where('id', $booking->hotel_id)->increment('booked_count');
        }

        if ($removeAccommodation) {
            $booking->accommodation = false;
            $booking->hotel_id = null;
            $booking->spouse_included = false;
            $booking->extras = 0;

            // Recalculate cost without accommodation
            $priceRow = EventPrices::where('event_id', $booking->event_id)
                ->where('member_type', ($booking->email ? 'Member' : 'Non-Member'))
                ->where('accommodation', false)
                ->first();
            if ($priceRow) {
                $booking->total_cost = $priceRow->price;
            }
        }

        $booking->booking_status = 'Pending Payment';
        $booking->cancellation_reason = null;
        $booking->admin_note = null;
        $booking->restored_at = now();
        $booking->save();

        // Generate new invoice
        $invoiceNumber = 'INV-' . strtoupper(uniqid());
        \App\Models\BookingInvoice::create([
            'booking_id' => $booking->bookingID,
            'invoice_number' => $invoiceNumber,
            'amount' => $booking->total_cost,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return redirect()->back()->with('status', 'Booking restored. New invoice sent to your email.');
    }

    public function adminUploadPoP(Request $request, $id)
    {
        $request->validate([
            'proof_of_payment' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $booking = Bookers::where('bookingID', $id)->first();

        if (!$booking) {
            return back()->with('error', 'Booking not found.');
        }

        // Delete old file if exists
        if ($booking->proof_of_payment &&
            Storage::disk('public')->exists($booking->proof_of_payment)) {
            Storage::disk('public')->delete($booking->proof_of_payment);
        }

        // Store new file
        $path = $request->file('proof_of_payment')
            ->store('proof_of_payments', 'public');

        $booking->update([
            'proof_of_payment' => $path,
            'booking_status' => 'Pending Payment',
        ]);

        // Send confirmation email to member
        try {
            $fullPath = Storage::disk('public')->path($path);
            Mail::to($booking->email)->send(new ProofOfPaymentEmail($booking, $fullPath));
        } catch (\Exception $e) {
            // Log but don't fail
        }

        return back()->with('success', 'Proof of payment uploaded successfully for ' . $booking->name . '.');
    }
}
