<?php

namespace App\Http\Controllers;

use App\Models\Bookers;
use App\Models\Participant;
use App\Models\Event;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportExportController extends Controller
{
    public function export(Request $request, $type)
    {
        $eventId = $request->event_id;
        $format = $request->format ?? 'xlsx';

        $event = Event::where('event_id', $eventId)->firstOrFail();

        switch ($type) {
            case 'participants': return $this->exportParticipants($event, $format);
            case 'bookings': return $this->exportBookings($event, $format);
            case 'revenue': return $this->exportRevenue($event, $format);
            case 'hotels': return $this->exportHotels($event, $format);
            case 'meals': return $this->exportMeals($event, $format);
            case 'attendance': return $this->exportAttendance($event, $format);
            default: return back()->with('error', 'Unknown report type.');
        }
    }

    private function exportParticipants($event, $format)
    {
        $participants = Participant::where('event_id', $event->event_id)
            ->select('reference_code', 'participant', 'email_address', 'phone_number', 'company_name', 'status', 'accommodation', 'is_walkin', 'meals')
            ->get()
            ->map(function ($p) {
                return [
                    'Reference' => $p->reference_code,
                    'Name' => $p->participant,
                    'Email' => $p->email_address,
                    'Phone' => $p->phone_number,
                    'Company' => $p->company_name,
                    'Status' => $p->status,
                    'Accommodation' => $p->accommodation ? 'Yes' : 'No',
                    'Walk-in' => $p->is_walkin ? 'Yes' : 'No',
                    'Meals' => $p->meals,
                ];
            })->toArray();

        $title = $event->event_name . ' - Participants';
        return $this->download($participants, $title, $format);
    }

    private function exportBookings($event, $format)
    {
        $bookings = Bookers::where('event_id', $event->event_id)
            ->select('booking_reference', 'name', 'email', 'company', 'member_type', 'booking_status', 'invoice_status', 'total_cost', 'amount_paid', 'balance', 'accommodation')
            ->get()
            ->map(function ($b) {
                return [
                    'Ref' => $b->booking_reference ?? $b->bookingID,
                    'Name' => $b->name,
                    'Email' => $b->email,
                    'Company' => $b->company,
                    'Member Type' => $b->member_type,
                    'Status' => $b->booking_status,
                    'Invoice' => $b->invoice_status,
                    'Total (MWK)' => number_format($b->total_cost, 2),
                    'Paid (MWK)' => number_format($b->amount_paid, 2),
                    'Balance (MWK)' => number_format($b->balance, 2),
                    'Accommodation' => $b->accommodation ? 'Yes' : 'No',
                ];
            })->toArray();

        $title = $event->event_name . ' - Bookings';
        return $this->download($bookings, $title, $format);
    }

    private function exportRevenue($event, $format)
    {
        $revenueBase = Bookers::where('event_id', $event->event_id)->whereNotIn('booking_status', ['Cancelled', 'Declined']);
        $totalInvoiced = (clone $revenueBase)->sum('total_cost');
        $totalPaid = (clone $revenueBase)->sum('amount_paid');
        $totalBalance = (clone $revenueBase)->sum('balance');

        $rows = [
            ['Metric', 'Value (MWK)'],
            ['Total Invoiced', number_format($totalInvoiced, 2)],
            ['Total Paid', number_format($totalPaid, 2)],
            ['Outstanding Balance', number_format($totalBalance, 2)],
            ['', ''],
            ['Booking', 'Amount (MWK)', 'Paid (MWK)', 'Balance (MWK)'],
        ];

        $bookings = Bookers::where('event_id', $event->event_id)
            ->select('booking_reference', 'name', 'total_cost', 'amount_paid', 'balance')
            ->get();

        foreach ($bookings as $b) {
            $rows[] = [
                $b->booking_reference ?? $b->bookingID,
                $b->name,
                number_format($b->total_cost, 2),
                number_format($b->amount_paid, 2),
                number_format($b->balance, 2),
            ];
        }

        $title = $event->event_name . ' - Revenue';
        return $this->download($rows, $title, $format, false);
    }

    private function exportHotels($event, $format)
    {
        $hotels = Hotel::where('event_id', $event->event_id)
            ->select('name', 'quantity', 'booked_count', 'available_count')
            ->get()
            ->map(function ($h) {
                return [
                    'Hotel' => $h->name,
                    'Total Rooms' => $h->quantity,
                    'Booked' => $h->booked_count,
                    'Available' => $h->available_count,
                    'Occupancy %' => $h->quantity > 0 ? round(($h->booked_count / $h->quantity) * 100) . '%' : 'N/A',
                ];
            })->toArray();

        $title = $event->event_name . ' - Hotels';
        return $this->download($hotels, $title, $format);
    }

    private function exportMeals($event, $format)
    {
        $totalCoupons = DB::table('meal_coupon')->where('event_id', $event->event_id)->count();
        $totalOffered = DB::table('meal_coupon')->where('event_id', $event->event_id)->sum('total_meals');
        $totalRedeemed = DB::table('meal_coupon')->where('event_id', $event->event_id)
            ->whereNotNull('meals_redeemed')->where('meals_redeemed', '!=', '')
            ->sum(DB::raw('CAST(meals_redeemed AS UNSIGNED)'));

        $masterCoupons = DB::table('master_meal_tags')->where('event_id', $event->event_id)->count();
        $masterMeals = DB::table('master_meal_tags')->where('event_id', $event->event_id)->sum('total_meals');
        $masterRedeemed = DB::table('meal_coupon')
            ->where('event_id', $event->event_id)
            ->whereIn('unique_code', function ($q) use ($event) {
                $q->select('unique_code')->from('master_meal_tags')->where('event_id', $event->event_id);
            })
            ->sum(DB::raw('CAST(meals_redeemed AS UNSIGNED)'));

        $rows = [
            ['Metric', 'Value'],
            ['Coupons Issued (Total)', $totalCoupons],
            ['Coupons Issued (Master)', $masterCoupons],
            ['Meals Offered (Total)', $totalOffered],
            ['Meals Offered (Master)', $masterMeals],
            ['Meals Redeemed (Total)', $totalRedeemed],
            ['Meals Redeemed (Master)', $masterRedeemed],
        ];

        $title = $event->event_name . ' - Meals';
        return $this->download($rows, $title, $format, false);
    }

    private function exportAttendance($event, $format)
    {
        $attendance = DB::table('event_sessions')
            ->leftJoin('attendance_registration', 'attendance_registration.session_id', '=', 'event_sessions.session_id')
            ->where('event_sessions.event_id', $event->event_id)
            ->select('event_sessions.session_date', 'event_sessions.description', DB::raw('COUNT(attendance_registration.id) as count'))
            ->groupBy('event_sessions.session_date', 'event_sessions.description')
            ->orderBy('event_sessions.session_date')
            ->get()
            ->map(function ($a) {
                return ['Date' => $a->session_date, 'Period' => $a->description ?? 'N/A', 'Attendees' => $a->count];
            })->toArray();

        $title = $event->event_name . ' - Attendance';
        return $this->download($attendance, $title, $format);
    }

    private function download($rows, $title, $format, $hasHeaders = true)
    {
        $filename = str_replace(' ', '_', $title) . '.' . $format;

        if ($format === 'csv') {
            $output = fopen('php://temp', 'w');
            foreach ($rows as $row) {
                fputcsv($output, (array)$row);
            }
            rewind($output);
            $content = stream_get_contents($output);
            fclose($output);

            return response($content, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        if ($format === 'xlsx') {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\ArrayExport(
                    $hasHeaders ? array_keys((array)$rows[0] ?? []) : $rows[0] ?? [],
                    $hasHeaders ? $rows : array_slice($rows, 1)
                ),
                $filename
            );
        }

        // PDF
        $pdf = Pdf::loadView('pdf.report', compact('rows', 'title', 'hasHeaders'));
        return $pdf->download($filename);
    }
}
