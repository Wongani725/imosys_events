<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\SponsorAd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Member;
use App\Models\Bookers;

use App\Models\Hotel;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\HotelBookingReportExport;
use App\Models\EventPrices;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\ParticipantNameTagMail;
use Illuminate\Support\Str;


class SponsorController extends Controller
{
    public function import_participants($id)
    {
        return view('participants.import', ['id' => $id]);
    }

    public function importData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'excel_file' => 'required|file|mimes:xls,xlsx',
            'event_id' => 'MEI-LK-2025'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $file = $request->file('excel_file');
        $event_id = 'MEI-LK-2025';

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rowLimit = $sheet->getHighestDataRow();

            $columnHeaders = [];
            foreach ($sheet->getRowIterator(1, 1) as $headerRow) {
                foreach ($headerRow->getCellIterator() as $cell) {
                    $columnHeaders[] = trim($cell->getValue());
                }
            }

            $importedCount = 0;
            $failedCount = 0;
            $errorMessages = [];

            for ($row = 2; $row <= $rowLimit; $row++) {
                $rowData = [];
                foreach ($sheet->getRowIterator($row, $row) as $dataRow) {
                    foreach ($dataRow->getCellIterator() as $cell) {
                        $rowData[] = trim($cell->getValue());
                    }
                }

                if (count(array_filter($rowData)) === 0) {
                    continue; // skip empty row
                }

                $data = array_combine($columnHeaders, $rowData);

                $participant = $data['Fullname'] ?? null;
                $company = $data['CompanyName'] ?? null;
                $email = $data['Email'] ?? null;
                $status = $data['Status'] ?? null;
                $amountPaid = $data['Amount Paid'] ?? 0;

                if (empty($participant) || empty($company) || empty($status)) {
                    $errorMessages[] = "Row $row: Missing participant, company, or status.";
                    $failedCount++;
                    continue;
                }

                // Get participation fees from event_prices
                $eventPrice = DB::table('event_prices')
                    ->where('event_id', $event_id)
                    ->where('status', $status)
                    ->value('price');
//                dd($eventPrice);

                if ($eventPrice === null) {
                    $errorMessages[] = "Row $row: No pricing found for status '$status'.";
                    $failedCount++;
                    continue;
                }

                // Generate unique reference code
                do {
                    $referenceCode = 'MEI-ERC-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT) . '-' . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
                    $exists = DB::table('event_participants')->where('reference_code', $referenceCode)->exists();
                } while ($exists);

                // Generate reference code
                $sanitizedCode = str_replace('/', '-', $referenceCode);

                // Insert into event_participants
                DB::table('event_participants')->insert([
                    'event_id' => $event_id,
                    'reference_code' => $referenceCode,
                    'participant' => $participant,
                    'company_name' => $company,
                    'email_address' => $email,
                    'status' => $status,
                    'participation_fees' => $eventPrice,
                    'amount_paid' => $amountPaid,
                    'total_amount' => $amountPaid,
                    'balance' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Insert into meal_coupon
                DB::table('meal_coupon')->insert([
                    'participant_reference_code' => $referenceCode,
                    'unique_code' => $referenceCode,
                    'total_meals' => 5,
                    'event_id' => $event_id,
                ]);

                $importedCount++;

                // Send email
                try {
                    if (!empty($email)) {
                        $emailData = [
                            'participant' => $participant,
                            'reference_code' => $referenceCode,
                            'event_id' => $event_id,
                            // add other data if needed
                        ];
                        Mail::to($email)->send(new ParticipantNameTagMail($emailData));
                    }
                } catch (\Exception $e) {
                    $errorMessages[] = "Row $row: Failed to send email to $email - " . $e->getMessage();
                }
            }

            $summary = "Import complete. Imported: $importedCount. Failed: $failedCount.";
            return back()->with('success', $summary)->withErrors($errorMessages);

        } catch (\Exception $e) {
            return back()->withErrors(['exception' => 'Error importing Excel: ' . $e->getMessage()]);
        }
    }


    public function viewInvoice1(Bookers $booking)
    {
        $user = $booking->user; // assuming Bookers has a relationship to User
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
            'user_city' => $user->city ?? '',
            'user_address' => $user->address ?? '',
            'title' => $user->title ?? '',
            'event_name' => $event->event_name ?? '',
            'start_date' => $event->start_date ?? '',
            'end_date' => $event->end_date ?? '',
            'event_price' => $eventPrice->price ?? 0
        ];

        if ($eventPrice) {
            $invoiceItems[] = [
                'qty' => 1,
                'description' => 'Registration fees',
                'unit' => $eventPrice->price,
                'total' => $eventPrice->price
            ];
        }

        if ($booking->hotel) {
            $invoiceItems[] = [
                'qty' => 1,
                'description' => 'Accommodation at ' . $booking->hotel->name,
                'unit' => $booking->hotel->extra_price ?? 0,
                'total' => $booking->hotel->extra_price ?? 0
            ];

            if ($booking->number_of_extra_people > 0) {
                $invoiceItems[] = [
                    'qty' => $booking->number_of_extra_people,
                    'description' => 'Extra Person at ' . $booking->hotel->name,
                    'unit' => $booking->hotel->extra_price,
                    'total' => $booking->hotel->extra_price * $booking->number_of_extra_people
                ];
            }
        }

        if ($booking->number_of_extra_meals > 0) {
            $invoiceItems[] = [
                'qty' => $booking->number_of_extra_meals,
                'description' => 'Extra Meal(s)',
                'unit' => $mealPrice,
                'total' => $mealPrice * $booking->number_of_extra_meals
            ];
        }

        $pdf = Pdf::loadView('pdf.booking_invoice', [
            'booking' => $booking,
            'breakdown' => $breakdown,
            'invoiceItems' => $invoiceItems,
        ]);

        return $pdf->stream('booking_invoice.pdf');
    }

    public function viewInvoice(string $bookingID)
    {
        $booking = Bookers::find($bookingID);
        if (!$booking) {
            return response()->json(['error' => 'Booking not found.'], 404);
        }

        $siblings = Bookers::where('booking_reference', $booking->booking_reference)
            ->where('bookingID', '!=', $booking->bookingID)
            ->get();

        $allBookings = $siblings->isNotEmpty() ? $siblings->prepend($booking) : collect([$booking]);

        $items = [];
        foreach ($allBookings as $b) {
            $event = Event::where('event_id', $b->event_id)->first();
            $memberType = $b->member_type ?? 'Member';

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
                ->when($priceHotelCode, function ($q) use ($priceHotelCode) {
                    return $q->where('hotel', $priceHotelCode);
                })
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

    public function getSponsors(){
        $event = Event::orderBy('created_at', 'desc')->first();
        $sponsors = SponsorAd::where('event_id', $event->event_id)
            ->orderBy('priority')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('sponsors.index', compact('sponsors', 'event'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sponsor' => 'required|string|max:255',
            'event_id' => 'required|exists:events,event_id',
            'file_path' => 'nullable|image|max:2048',
            'priority' => 'nullable|integer|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($request->hasFile('file_path')) {
            $file = $request->file('file_path');
            $fileName = time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('sponsors'), $fileName);
            $data['file_path'] = 'sponsors/' . $fileName;
        }

        SponsorAd::create($data);

        return redirect()->back()->with('success', 'Sponsor added successfully.');
    }

    public function update(Request $request, $id)
    {
        $sponsor = SponsorAd::findOrFail($id);

        $data = $request->validate([
            'sponsor' => 'required|string|max:255',
            'file_path' => 'nullable|image|max:2048',
            'priority' => 'nullable|integer|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($request->hasFile('file_path')) {
            $file = $request->file('file_path');
            $fileName = time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('sponsors'), $fileName);
            $data['file_path'] = 'sponsors/' . $fileName;
        }

        $sponsor->update($data);

        return redirect()->back()->with('success', 'Sponsor updated successfully.');
    }

    public function destroy($id)
    {
        $sponsor = SponsorAd::findOrFail($id);

        // Optionally delete the file
        if ($sponsor->file_path) {
            $filePath = public_path($sponsor->file_path);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $sponsor->delete();

        return redirect()->back()->with('success', 'Sponsor deleted successfully.');
    }


    public function updateMember(Request $request)
    {
        $member = Member::findOrFail($request->id);
        $changesMade = false;

        $memberUpdates = [];
        $bookerUpdates = [];

        // --- Full Name (participant) ---
        if ($request->participant !== null && $request->participant !== $member->participant) {
            $memberUpdates['participant'] = $request->participant;

            // Optional: if bookers have a name field
            $bookerUpdates['name'] = $request->participant;
        }

        // --- Company Name ---
        if ($request->company_name !== null && $request->company_name !== $member->company_name) {
            $memberUpdates['company_name'] = $request->company_name;
            $bookerUpdates['company'] = $request->company_name;
        }

        // --- Address ---
        if ($request->address !== null && $request->address !== $member->address) {
            $memberUpdates['address'] = $request->address;
        }

        // --- Phone Number ---
        if ($request->phone_number !== null && $request->phone_number !== $member->phone_number) {
            $memberUpdates['phone_number'] = $request->phone_number;
            $bookerUpdates['phone_number'] = $request->phone_number;
        }

        // --- Email ---
        if ($request->email_address !== null && $request->email_address !== $member->email_address) {
            $memberUpdates['email_address'] = $request->email_address;
            $bookerUpdates['email'] = $request->email_address;
        }

        // --- Date Joined ---
        if ($request->datejoined !== null && $request->datejoined !== $member->datejoined) {
            $memberUpdates['datejoined'] = $request->datejoined;
        }

        // --- Update member ---
        if (!empty($memberUpdates)) {
            $member->update($memberUpdates);
            $changesMade = true;
        }

        // --- Update linked bookers ---
        if (!empty($bookerUpdates)) {
            $updated = Bookers::where('bookingID', $member->reference_code)->update($bookerUpdates);
            if ($updated) {
                $changesMade = true;
            }
        }

        return redirect()->back()->with(
            'success',
            $changesMade ? 'Member updated successfully.' : 'No changes were made.'
        );
    }
    public function add_member(Request $request)
    {
        $request->validate([
            'participant'   => 'required|string|max:255',
            'company_name'  => 'required|string|max:255',
            'email_address' => 'required|email|unique:members,email_address',
            'phone_number'  => 'nullable|string|max:20',
            'address'       => 'nullable|string|max:255',
            'datejoined'    => 'nullable|date',
            'status'    =>     'required|string'
        ]);

        // Get last reference code
        $lastMember = DB::table('members')
            ->where('reference_code', 'like', 'MLS-26-%')
            ->orderByDesc('id')
            ->first();

        if ($lastMember) {
            $lastNumber = (int) str_replace('MLS-26-', '', $lastMember->reference_code);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        $referenceCode = 'MLS-26-' . $nextNumber;

        // generate approval code
        $approvalCode = strtoupper(Str::random(10));

        DB::table('members')->insert([
            'participant'    => $request->participant,
            'company_name'   => $request->company_name,
            'email_address'  => $request->email_address,
            'phone_number'   => $request->phone_number,
            'address'        => $request->address,
            'datejoined'     => $request->datejoined,
            'reference_code' => $referenceCode,
            'approval_code'  => $approvalCode,
            'pending_status' => 'pending approval',
            'status'         => $request->status,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return back()->with('success', 'Member added successfully.');
    }

    // Show the preview of the report
    public function showReport(Request $request)
    {
        $event_id = 'ICAM-LK-2025';

        try {
            $hotels = Hotel::where('event_id', $event_id)
                ->with('bookers')
                ->get();
        } catch (\Exception $exception) {
            return redirect()->back()->withInput()->withErrors(["exception" => "{$exception->getMessage()}"]);
        }

        return view('Reports.hotel_booking_report', compact('hotels', 'event_id'));
    }


    public function export()
    {
        $event_id = 'ICAM-LK-2025';

        $hotels = Hotel::where('event_id', $event_id)
            ->with('bookers')
            ->get();

        $output = '
        <table border="1">
            <thead>
                <tr>
                    <th>Hotel</th>
                    <th>Participant Name</th>
                    <th>Email</th>
                    <th>Organization</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($hotels as $hotel) {
            foreach ($hotel->bookers as $booker) {
                $output .= '<tr>
                    <td>' . htmlspecialchars($hotel->name) . '</td>
                    <td>' . htmlspecialchars($booker->name) . '</td>
                    <td>' . htmlspecialchars($booker->email) . '</td>
                    <td>' . htmlspecialchars($booker->company ?? 'N/A') . '</td>
                    <td>' . htmlspecialchars($booker->booking_status) . '</td>
                    <td>' . htmlspecialchars($booker->statusInfo->status ?? 'N/A') . '</td>
                    <td>' . number_format($booker->statusInfo->price ?? 0) . '</td>
                    <td>' . number_format($booker->total_cost, 2) . '</td>
                </tr>';
                }
            }

        $output .= '</tbody></table>';

        return response($output)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="hotel_booking_report.xls"');
    }

    public function showConferenceReport()
    {
        $event_id = 'ICAM-LK-2025';

        try {
            // Fetch bookings without a room or hotel
            $bookings = Bookers::with(['statusInfo'])
                ->whereNull('room_type')
                ->where('event_id', $event_id)
                ->get();

        } catch (\Exception $exception) {
            return redirect()->back()->withErrors(['exception' => $exception->getMessage()]);
        }

        return view('Reports.conference_booking_report', compact('bookings', 'event_id'));
    }

    public function exportNoAccommodation()
    {
        $event_id = 'ICAM-LK-2025';

        $bookings = Bookers::with('statusInfo')
            ->where('event_id', $event_id)
            ->whereNull('room_type')
            ->get();

        $output = '
    <table border="1">
        <thead>
            <tr>
                <th>Participant Name</th>
                <th>Email</th>
                <th>Organization</th>
                <th>Status</th>
                <th>Participation Fee (MWK)</th>
                <th>Total (MWK)</th>
                <th>Booking Date</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($bookings as $booker) {
            $output .= '<tr>
            <td>' . htmlspecialchars($booker->name) . '</td>
            <td>' . htmlspecialchars($booker->email) . '</td>
            <td>' . htmlspecialchars($booker->company ?? 'N/A') . '</td>
            <td>' . htmlspecialchars($booker->statusInfo->status ?? 'N/A') . '</td>
            <td>' . number_format($booker->statusInfo->price ?? 0) . '</td>
            <td>' . number_format($booker->total_cost, 2) . '</td>
            <td>' . $booker->created_at->format('Y-m-d') . '</td>
        </tr>';
        }

        $output .= '</tbody></table>';

        return response($output)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="bookings_without_accommodation.xls"');
    }


    public function adminUploadReceipt(Request $request, $id)
    {
        $request->validate([
            'receipt_number'   => 'required|string|max:255',
            'amount_paid'      => 'required|numeric|min:0',
            'date_verified'    => 'nullable|date',
        ]);

        $booking = Bookers::findOrFail($id);

        // Calculate balance
        $amountPaid = $request->input('amount_paid');
        $balance = $amountPaid - $booking->total_cost;

//        dd($balance);


        // Update booking record
        $booking->receipt_number = $request->input('receipt_number');
        $booking->amount_paid = $amountPaid;
        $booking->balance = $balance;
        $booking->date_verified = $request->input('date_verified');
        $booking->save();

        return redirect()->back()->with('success', 'Receipt attached successfully.');
    }

}

