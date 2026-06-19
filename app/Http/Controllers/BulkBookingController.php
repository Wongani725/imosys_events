<?php

namespace App\Http\Controllers;

use App\Models\Bookers;
use App\Models\Event;
use App\Models\EventPrices;
use App\Models\Hotel;
use App\Models\Member;
use App\Models\BookingInvoice;
use App\Models\AttireSize;
use App\Mail\BulkBookingInvoiceMail;
use App\Mail\BulkBookingNotificationMail;
use App\Models\Notification;
use App\Models\NotificationRecipient;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class BulkBookingController extends Controller
{
    public function index()
    {
        $events = Event::where('event_status', 'active')->get();
        return view('admin.bulk-booking.index', compact('events'));
    }

    public function downloadTemplate(Request $request)
    {
        $headers = ['event', 'member_status', 'member_id', 'name', 'email', 'phone', 'accommodation', 'hotel_name', 'spouse_included', 'extras', 'attire_size'];

        $activeEvents = Event::where('event_status', 'active')->orderBy('start_date')->get();
        $eventNames = $activeEvents->pluck('event_name')->toArray();
        $govName = $eventNames[0] ?? 'Governance Forum';
        $annualName = $eventNames[1] ?? 'Annual Conference';

        $sample = [
            [$govName, 'Member', 'MEM-001', 'John Doe', 'john@org.com', '0999123456', 'Yes', 'Sunbird Nkopola', 'No', '0', 'XL'],
            [$govName, 'Member', 'MEM-002', 'Jane Doe', 'jane@org.com', '0999123456', 'Yes', 'Sunbird Nkopola', 'Yes', '0', 'L'],
            [$annualName, 'Member', 'MEM-001', 'John Doe', 'john@org.com', '0999123456', 'Yes', 'Sun N Sand Holiday Resort', 'Yes', '1', 'XL'],
            [$annualName, 'Non-Member', '', 'Bob Smith', 'bob@org.com', '0999123457', 'No', '', 'No', '0', ''],
            [$govName, 'Non-Member', '', 'Alice Brown', 'alice@org.com', '0999123458', 'Yes', 'Sunbird Nkopola', 'No', '0', 'M'],
            [$annualName, 'Non-Member', '', 'Eve White', 'eve@org.com', '0999123459', 'No', '', 'No', '0', ''],
        ];

        return Excel::download(
            new \App\Exports\BulkBookingTemplateExport($headers, $sample),
            'bulk_booking_template.xlsx'
        );
    }

    protected function parseAndValidateRows($rows, $orgName)
    {
        $validRows = [];
        $errors = [];
        $warnings = [];
        $hotelAllocations = [];

        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex === 0) continue;

            $eventName = trim($row[0] ?? '');
            $memberStatus = trim($row[1] ?? '');
            $memberId = trim($row[2] ?? '');
            $participantName = trim($row[3] ?? '');
            $email = trim($row[4] ?? '');
            $phone = trim($row[5] ?? '');

            $rowNum = $rowIndex + 1;

            if (empty($participantName) || empty($email)) {
                $errors[] = ['row' => $rowNum, 'name' => $participantName, 'email' => $email, 'issue' => 'Name and email are required'];
                continue;
            }

            if (empty($eventName)) {
                $errors[] = ['row' => $rowNum, 'name' => $participantName, 'email' => $email, 'issue' => 'Event is required'];
                continue;
            }

            $event = Event::where('event_name', $eventName)->where('event_status', 'active')->first();
            if (!$event) {
                $errors[] = ['row' => $rowNum, 'name' => $participantName, 'email' => $email, 'issue' => "Event '{$eventName}' not found"];
                continue;
            }
            $eventId = $event->event_id;
            $eventSelection = $event->event_type ?? 'main';

            $isMember = strtolower($memberStatus) === 'member';

            if ($isMember && empty($memberId)) {
                $errors[] = ['row' => $rowNum, 'name' => $participantName, 'email' => $email, 'issue' => 'Member ID required for Members'];
                continue;
            }

            $existing = Bookers::where('email', $email)
                ->where('event_id', $eventId)
                ->whereIn('booking_status', ['Pending Payment', 'Confirmed'])
                ->first();

            if ($existing) {
                $errors[] = ['row' => $rowNum, 'name' => $participantName, 'email' => $email, 'issue' => "Already booked for {$eventName}"];
                continue;
            }

            $accommodation = strtolower(trim($row[6] ?? '')) === 'yes';
            $hotelName = trim($row[7] ?? '');
            $spouse = strtolower(trim($row[8] ?? '')) === 'yes';
            $extras = (int)($row[9] ?? 0);
            $attireSizeName = trim($row[10] ?? '');

            $hotel = null;
            $hotelCode = null;
            $warning = null;

            if ($accommodation && !empty($hotelName)) {
                $hotel = Hotel::where('name', $hotelName)->where('event_id', $eventId)->first();
                if (!$hotel) {
                    $errors[] = ['row' => $rowNum, 'name' => $participantName, 'email' => $email, 'issue' => "Hotel '{$hotelName}' not found for {$eventName}"];
                    continue;
                }
                $allocated = $hotelAllocations[$hotel->id] ?? 0;
                if ($hotel->available_count - $allocated <= 0) {
                    $warning = "{$hotelName} fully booked (all {$hotel->available_count} rooms taken). Created without accommodation. Spouse and extras removed.";
                    $accommodation = false;
                    $spouse = false;
                    $extras = 0;
                    $hotel = null;
                } else {
                    $hotelAllocations[$hotel->id] = $allocated + 1;
                    $hotelCode = str_contains(strtolower($hotelName), 'nkopola') ? 'nkopola' : 'sun_n_sand';
                }
            }

            $priceRow = EventPrices::where('event_id', $eventId)
                ->where('member_type', $isMember ? 'Member' : 'Non-Member')
                ->where('accommodation', $accommodation)
                ->when($accommodation, function ($q) use ($hotelCode, $spouse) {
                    return $q->where('hotel', $hotelCode)->where('spouse_included', $spouse);
                })
                ->first();

            if (!$priceRow) {
                $errors[] = ['row' => $rowNum, 'name' => $participantName, 'email' => $email, 'issue' => 'No pricing found for selected options'];
                continue;
            }

            $totalCost = $priceRow->price + ($extras * $priceRow->extra_person_price);

            $attireSizeRecord = null;
            if (!empty($attireSizeName)) {
                $attireSizeRecord = AttireSize::where('name', $attireSizeName)
                    ->where('event_id', $eventId)->first();
                if (!$attireSizeRecord) {
                    $errors[] = ['row' => $rowNum, 'name' => $participantName, 'email' => $email, 'issue' => "Attire size '{$attireSizeName}' not found for {$eventName}"];
                }
            }

            $validRows[] = [
                'event_name' => $eventName,
                'event_id' => $eventId,
                'event_selection' => $eventSelection,
                'member_status' => $memberStatus,
                'member_id' => $memberId,
                'name' => $participantName,
                'email' => $email,
                'phone' => $phone,
                'accommodation' => $accommodation,
                'hotel_name' => $hotelName,
                'hotel_id' => $hotel ? $hotel->id : null,
                'spouse_included' => $spouse,
                'extras' => $extras,
                'attire_size_name' => $attireSizeName,
                'attire_size_id' => $attireSizeRecord ? $attireSizeRecord->id : null,
                'total_cost' => $totalCost,
                'warning' => $warning,
            ];
        }

        return [$validRows, $errors, $warnings];
    }

    public function preview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_name' => 'required|string|max:255',
            'contact_email' => 'required|email',
            'excel_file' => 'required|file|mimes:xls,xlsx,csv',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $orgName = $request->organization_name;
        $contactEmail = $request->contact_email;
        $rows = Excel::toArray([], $request->file('excel_file'));

        if (empty($rows) || empty($rows[0])) {
            return back()->with('error', 'Excel file is empty.');
        }

        list($validRows, $errorRows, $warnings) = $this->parseAndValidateRows($rows[0], $orgName);

        $totalAmount = collect($validRows)->sum('total_cost');
        $peopleWithAcc = collect($validRows)->where('accommodation', true)->count();
        $hotelWarnings = collect($validRows)->filter(fn($r) => !empty($r['warning']))->values();

        // Store the uploaded file and form data in session for confirmation
        $path = $request->file('excel_file')->store('temp/bulk-imports');
        session([
            'bulk_import_file' => $path,
            'bulk_import_org' => $orgName,
            'bulk_import_email' => $contactEmail,
        ]);

        return view('admin.bulk-booking.preview', compact(
            'validRows', 'errorRows', 'orgName', 'contactEmail',
            'totalAmount', 'peopleWithAcc', 'hotelWarnings'
        ));
    }

    public function confirmImport(Request $request)
    {
        $path = session('bulk_import_file');
        $orgName = session('bulk_import_org');
        $contactEmail = session('bulk_import_email');

        if (!$path || !Storage::exists($path)) {
            return redirect()->route('admin.bulk-booking.index')->with('error', 'Import session expired. Please upload again.');
        }

        $rows = Excel::toArray([], storage_path('app/' . $path));
        list($validRows, $errors, $warnings) = $this->parseAndValidateRows($rows[0] ?? [], $orgName);

        if (empty($validRows)) {
            Storage::delete($path);
            session()->forget(['bulk_import_file', 'bulk_import_org', 'bulk_import_email']);
            return redirect()->route('admin.bulk-booking.index')->with('error', 'No valid rows to import.');
        }

        $batchRef = 'BULK-' . strtoupper(Str::random(6)) . '-' . now()->format('Ymd');
        $imported = 0;
        $importErrors = [];

        DB::beginTransaction();
        try {
            foreach ($validRows as $r) {
                $member = null;
                if (strtolower($r['member_status']) === 'member' && !empty($r['member_id'])) {
                    $member = Member::where('member_id', $r['member_id'])->orWhere('email_address', $r['email'])->first();
                    if (!$member) {
                        $member = Member::create([
                            'member_id' => $r['member_id'],
                            'participant' => $r['name'],
                            'email_address' => $r['email'],
                            'phone_number' => $r['phone'],
                            'company_name' => $orgName,
                            'status' => 'Member',
                        ]);
                    }
                }

                $booking = Bookers::create([
                    'booking_reference' => $batchRef,
                    'event_id' => $r['event_id'],
                    'event_selection' => $r['event_selection'],
                    'accommodation' => $r['accommodation'],
                    'hotel_id' => $r['hotel_id'],
                    'spouse_included' => $r['spouse_included'],
                    'extras' => $r['extras'],
                    'attire_size_id' => $r['attire_size_id'],
                    'name' => $r['name'],
                    'email' => $r['email'],
                    'phone_number' => $r['phone'],
                    'company' => $orgName,
                    'member_type' => strtolower($r['member_status']) === 'member' ? 'Member' : 'Non-Member',
                    'memberID' => $member ? ($member->member_id ?? $member->reference_code) : null,
                    'booking_status' => 'Pending Payment',
                    'invoice_status' => 'pending',
                    'total_cost' => $r['total_cost'],
                    'balance' => $r['total_cost'],
                ]);

                if ($r['accommodation'] && $r['hotel_id']) {
                    Hotel::where('id', $r['hotel_id'])->where('available_count', '>', 0)->decrement('available_count');
                    Hotel::where('id', $r['hotel_id'])->increment('booked_count');
                }

                BookingInvoice::create([
                    'booking_id' => $booking->bookingID,
                    'invoice_number' => 'INV-BULK-' . strtoupper(uniqid()),
                    'amount' => $r['total_cost'],
                    'status' => 'pending',
                    'sent_at' => now(),
                ]);

                $imported++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Storage::delete($path);
            session()->forget(['bulk_import_file', 'bulk_import_org', 'bulk_import_email']);
            return redirect()->route('admin.bulk-booking.index')->with('error', 'Import failed: ' . $e->getMessage());
        }

        Storage::delete($path);
        session()->forget(['bulk_import_file', 'bulk_import_org', 'bulk_import_email']);

        // Send invoice
        try {
            $bookings = Bookers::with('hotel', 'event')->where('booking_reference', $batchRef)->get();
            $totalAmount = $bookings->sum('total_cost');
            $eventNames = $bookings->pluck('event.event_name')->unique()->implode(' & ');
            Mail::to($contactEmail)->send(new BulkBookingInvoiceMail($orgName, $eventNames, $batchRef, $bookings, $totalAmount));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Bulk booking invoice email failed: ' . $e->getMessage());
        }

        $msg = "Successfully created {$imported} bookings.";
        if ($imported > 0) $msg .= " Invoice sent to {$contactEmail}.";
        if (!empty($errors)) {
            $msg .= " " . count($errors) . " row(s) had errors and were skipped.";
        }

        return redirect()->route('admin.bulk-booking.index')->with('success', $msg);
    }

    // Keep the old import for backward compatibility or direct use
    public function import(Request $request)
    {
        return $this->preview($request);
    }

    public function memberPreview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_name' => 'required|string|max:255',
            'contact_email' => 'required|email',
            'excel_file' => 'required|file|mimes:xls,xlsx,csv',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $orgName = $request->organization_name;
        $contactEmail = $request->contact_email;
        $rows = Excel::toArray([], $request->file('excel_file'));

        if (empty($rows) || empty($rows[0])) {
            return back()->with('error', 'Excel file is empty.');
        }

        list($validRows, $errorRows, $warnings) = $this->parseAndValidateRows($rows[0], $orgName);

        $totalAmount = collect($validRows)->sum('total_cost');
        $peopleWithAcc = collect($validRows)->where('accommodation', true)->count();
        $hotelWarnings = collect($validRows)->filter(fn($r) => !empty($r['warning']))->values();

        $path = $request->file('excel_file')->store('temp/bulk-imports');
        session([
            'member_bulk_import_file' => $path,
            'member_bulk_import_org' => $orgName,
            'member_bulk_import_email' => $contactEmail,
        ]);

        return view('web_booking.bulk-preview', compact(
            'validRows', 'errorRows', 'orgName', 'contactEmail',
            'totalAmount', 'peopleWithAcc', 'hotelWarnings'
        ));
    }

    public function memberConfirmImport(Request $request)
    {
        $path = session('member_bulk_import_file');
        $orgName = session('member_bulk_import_org');
        $contactEmail = session('member_bulk_import_email');

        if (!$path || !Storage::exists($path)) {
            return redirect()->route('member-dashboard')->with('error', 'Import session expired. Please upload again.');
        }

        $rows = Excel::toArray([], storage_path('app/' . $path));
        list($validRows, $errors, $warnings) = $this->parseAndValidateRows($rows[0] ?? [], $orgName);

        if (empty($validRows)) {
            Storage::delete($path);
            session()->forget(['member_bulk_import_file', 'member_bulk_import_org', 'member_bulk_import_email']);
            return redirect()->route('member-dashboard')->with('error', 'No valid rows to import.');
        }

        $batchRef = 'BULK-' . strtoupper(Str::random(6)) . '-' . now()->format('Ymd');
        $imported = 0;
        $importErrors = [];

        DB::beginTransaction();
        try {
            foreach ($validRows as $r) {
                $member = null;
                if (strtolower($r['member_status']) === 'member' && !empty($r['member_id'])) {
                    $member = Member::where('member_id', $r['member_id'])->orWhere('email_address', $r['email'])->first();
                    if (!$member) {
                        $member = Member::create([
                            'member_id' => $r['member_id'],
                            'participant' => $r['name'],
                            'email_address' => $r['email'],
                            'phone_number' => $r['phone'],
                            'company_name' => $orgName,
                            'status' => 'Member',
                        ]);
                    }
                }

                $booking = Bookers::create([
                    'booking_reference' => $batchRef,
                    'event_id' => $r['event_id'],
                    'event_selection' => $r['event_selection'],
                    'accommodation' => $r['accommodation'],
                    'hotel_id' => $r['hotel_id'],
                    'spouse_included' => $r['spouse_included'],
                    'extras' => $r['extras'],
                    'attire_size_id' => $r['attire_size_id'],
                    'name' => $r['name'],
                    'email' => $r['email'],
                    'phone_number' => $r['phone'],
                    'company' => $orgName,
                    'member_type' => strtolower($r['member_status']) === 'member' ? 'Member' : 'Non-Member',
                    'memberID' => $member ? ($member->member_id ?? $member->reference_code) : null,
                    'booking_status' => 'Pending Payment',
                    'invoice_status' => 'pending',
                    'total_cost' => $r['total_cost'],
                    'balance' => $r['total_cost'],
                ]);

                if ($r['accommodation'] && $r['hotel_id']) {
                    Hotel::where('id', $r['hotel_id'])->where('available_count', '>', 0)->decrement('available_count');
                    Hotel::where('id', $r['hotel_id'])->increment('booked_count');
                }

                BookingInvoice::create([
                    'booking_id' => $booking->bookingID,
                    'invoice_number' => 'INV-BULK-' . strtoupper(uniqid()),
                    'amount' => $r['total_cost'],
                    'status' => 'pending',
                    'sent_at' => now(),
                ]);

                $imported++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Storage::delete($path);
            session()->forget(['member_bulk_import_file', 'member_bulk_import_org', 'member_bulk_import_email']);
            return redirect()->route('member-dashboard')->with('error', 'Import failed: ' . $e->getMessage());
        }

        Storage::delete($path);
        session()->forget(['member_bulk_import_file', 'member_bulk_import_org', 'member_bulk_import_email']);

        // Send invoice
        try {
            $bookings = Bookers::with('hotel', 'event')->where('booking_reference', $batchRef)->get();
            $totalAmount = $bookings->sum('total_cost');
            $eventNames = $bookings->pluck('event.event_name')->unique()->implode(' & ');
            Mail::to($contactEmail)->send(new BulkBookingInvoiceMail($orgName, $eventNames, $batchRef, $bookings, $totalAmount));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Bulk booking invoice email failed: ' . $e->getMessage());
        }

        $msg = "Successfully created {$imported} bookings.";
        if ($imported > 0) $msg .= " Invoice sent to {$contactEmail}.";
        if (!empty($errors)) {
            $msg .= " " . count($errors) . " row(s) had errors and were skipped.";
        }

        return redirect()->route('member-dashboard')->with('success', $msg);
    }

    public function approveBatch($batchRef)
    {
        $bookings = Bookers::where('booking_reference', $batchRef)
            ->where('booking_status', 'Pending Payment')
            ->get();

        if ($bookings->isEmpty()) {
            return back()->with('error', 'No pending bookings found for this batch.');
        }

        $confirmed = 0;
        $errors = [];

        foreach ($bookings as $booker) {
            try {
                DB::transaction(function () use ($booker) {
                    $memberId = $booker->memberID ?? 'BULK-' . $booker->bookingID;

                    $totalMeals = \App\Helpers\MealCalculator::calculate(
                        $booker->event->event_type ?? 'main',
                        $booker->accommodation
                    );

                    DB::table('event_participants')->insert([
                        'event_id' => $booker->event_id,
                        'reference_code' => $memberId,
                        'participant' => $booker->name,
                        'email_address' => $booker->email,
                        'company_name' => $booker->company,
                        'status' => $booker->member_type,
                        'accommodation' => $booker->accommodation,
                        'hotel_id' => $booker->accommodation ? $booker->hotel_id : null,
                        'event_selection' => $booker->event_selection,
                        'booker_id' => $booker->bookingID,
                        'meals' => $totalMeals,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('meal_coupon')->insert([
                        'participant_reference_code' => $memberId,
                        'unique_code' => $memberId,
                        'total_meals' => $totalMeals,
                        'event_id' => $booker->event_id,
                        'status' => 'main',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $booker->booking_status = 'Confirmed';
                    $booker->reference_code = $memberId;
                    $booker->invoice_status = 'paid';
                    $booker->date_paid = now();
                    $booker->save();
                });
                $confirmed++;
            } catch (\Exception $e) {
                $errors[] = "{$booker->name}: " . $e->getMessage();
            }
        }

        $message = "Batch approved: {$confirmed} bookings confirmed.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode('; ', $errors);
        }

        return redirect()->route('admin.bulk-booking.index')->with('success', $message);
    }

    public function notifyBatch(Request $request, $batchRef)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $bookings = Bookers::where('booking_reference', $batchRef)->get();

        if ($bookings->isEmpty()) {
            return back()->with('error', 'Batch not found.');
        }

        $title = $request->title;
        $body = $request->message;

        // Create in-app notification
        $notification = Notification::create([
            'title' => $title,
            'message' => $body,
            'audience_type' => 'bulk_batch',
            'created_by' => $request->user()->id,
        ]);

        $recipientBatch = [];
        $emailErrors = [];

        foreach ($bookings as $booking) {
            // In-app notification for members
            if ($booking->memberID) {
                $member = \App\Models\Member::where('reference_code', $booking->memberID)->first();

                if ($member) {
                    $recipientBatch[] = [
                        'notification_id' => $notification->id,
                        'member_id' => $member->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Email notification
            try {
                Mail::to($booking->email)->send(new BulkBookingNotificationMail(
                    $title,
                    $body,
                    $booking->name,
                    $batchRef
                ));
            } catch (\Exception $e) {
                $emailErrors[] = $booking->name;
                \Illuminate\Support\Facades\Log::error('Bulk notification email failed for ' . $booking->email . ': ' . $e->getMessage());
            }
        }

        if (!empty($recipientBatch)) {
            NotificationRecipient::insert($recipientBatch);
        }

        $sentCount = $bookings->count();
        $msg = "Notification sent to {$sentCount} bookers via email.";
        if (!empty($recipientBatch)) {
            $msg .= " In-app notification sent to " . count($recipientBatch) . " members.";
        }
        if (!empty($emailErrors)) {
            $msg .= " Email failed for: " . implode(', ', $emailErrors);
        }

        return redirect()->route('admin.bulk-booking.index')->with('success', $msg);
    }

    public function viewInvoice($batchRef)
    {
        $bookings = Bookers::with('hotel', 'event')->where('booking_reference', $batchRef)->get();
        if ($bookings->isEmpty()) {
            return back()->with('error', 'Batch not found.');
        }

        $orgName = $bookings->first()->company;
        $eventNames = $bookings->pluck('event.event_name')->unique()->implode(', ');
        $totalAmount = $bookings->sum('total_cost');
        $totalPeople = $bookings->count();
        $peopleWithAccommodation = $bookings->where('accommodation', true)->count();

        $pdf = Pdf::loadView('pdf.bulk_booking_invoice', compact(
            'bookings', 'orgName', 'eventNames', 'totalAmount', 'batchRef', 'totalPeople', 'peopleWithAccommodation'
        ));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('invoice_' . $batchRef . '.pdf');
    }
}
