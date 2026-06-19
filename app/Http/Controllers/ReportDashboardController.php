<?php

namespace App\Http\Controllers;

use App\Models\Bookers;
use App\Models\Event;
use App\Models\Hotel;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportDashboardController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::orderBy('start_date', 'desc')->get();
        $selectedEvent = $events->first();
        if ($request->event_id) {
            $selectedEvent = Event::where('event_id', $request->event_id)->firstOrFail();
        }

        $reports = [
            ['type' => 'participants', 'title' => 'Participants', 'desc' => 'All registered participants with contact details, status, and accommodation.'],
            ['type' => 'bookings', 'title' => 'Bookings', 'desc' => 'All bookings with status, payment info, and member type.'],
            ['type' => 'revenue', 'title' => 'Revenue', 'desc' => 'Total invoiced, paid, and outstanding per booking.'],
            ['type' => 'hotels', 'title' => 'Hotel Occupancy', 'desc' => 'Room allocation per hotel with occupancy %.'],
            ['type' => 'meals', 'title' => 'Meals', 'desc' => 'Coupons issued, meals offered and redeemed.'],
            ['type' => 'attendance', 'title' => 'Attendance', 'desc' => 'Session attendance per day and period.'],
            ['type' => 'evaluation-participation', 'title' => 'Evaluation Response Rate', 'desc' => 'How many confirmed participants submitted evaluations per event.'],
            ['type' => 'evaluation-scores', 'title' => 'Evaluation Scores by Section', 'desc' => 'Average scores per section (radio questions).'],
            ['type' => 'evaluation-speakers', 'title' => 'Speaker Ratings', 'desc' => 'Average rating per speaker, distribution, and comments.'],
            ['type' => 'evaluation-feedback', 'title' => 'Open-Ended Feedback', 'desc' => 'All text answers listed per question.'],
            ['type' => 'evaluation-individual', 'title' => 'Individual Answers', 'desc' => 'See how each participant answered per question.'],
            ['type' => 'evaluation-certificates', 'title' => 'Certificate Eligibility', 'desc' => 'Participants who completed evaluation and are eligible for certificates.'],
        ];

        return view('admin.reports.index', compact('events', 'selectedEvent', 'reports'));
    }

    public function show(Request $request, $type)
    {
        $events = Event::orderBy('start_date', 'desc')->get();
        $selectedEvent = $events->first();
        if ($request->event_id) {
            $selectedEvent = Event::where('event_id', $request->event_id)->firstOrFail();
        }

        $eventId = $selectedEvent->event_id;
        $data = [];

        switch ($type) {
            case 'participants':
                $query = Participant::where('event_id', $eventId)
                    ->select('reference_code', 'participant', 'email_address', 'phone_number', 'company_name', 'status', 'accommodation', 'is_walkin', 'meals');
                $currentFilter = $request->filter;
                if ($currentFilter === 'with_accommodation') {
                    $query->where('accommodation', true);
                } elseif ($currentFilter === 'without_accommodation') {
                    $query->where('accommodation', false);
                }
                $data = $query->limit(100)->get();
                return view('admin.reports.participants', compact('events', 'selectedEvent', 'data', 'currentFilter'));

            case 'bookings':
                $data = Bookers::where('event_id', $eventId)
                    ->select('booking_reference', 'bookingID', 'name', 'email', 'company', 'member_type', 'booking_status', 'invoice_status', 'total_cost', 'amount_paid', 'balance', 'accommodation', 'created_at')
                    ->limit(50)
                    ->get()
                    ->map(function ($b) {
                        return [
                            'Reference' => $b->booking_reference ?? $b->bookingID,
                            'Name' => $b->name,
                            'Email' => $b->email,
                            'Company' => $b->company,
                            'Type' => $b->member_type,
                            'Status' => $b->booking_status,
                            'Invoice' => $b->invoice_status,
                            'Accommodation' => $b->accommodation ? 'Yes' : 'No',
                            'Total (MWK)' => number_format($b->total_cost, 2),
                            'Paid (MWK)' => number_format($b->amount_paid, 2),
                            'Balance (MWK)' => number_format($b->balance, 2),
                            'Date' => $b->created_at ? \Carbon\Carbon::parse($b->created_at)->format('d M Y') : '—',
                        ];
                    });
                break;

            case 'revenue':
                $revenueBase = Bookers::where('event_id', $eventId)->whereNotIn('booking_status', ['Cancelled', 'Declined']);
                $totalInvoiced = (clone $revenueBase)->sum('total_cost');
                $totalPaid = (clone $revenueBase)->sum('amount_paid');
                $totalBalance = (clone $revenueBase)->sum('balance');
                $bookings = Bookers::where('event_id', $eventId)
                    ->whereNotIn('booking_status', ['Cancelled', 'Declined'])
                    ->select('booking_reference', 'name', 'total_cost', 'amount_paid', 'balance')
                    ->limit(50)->get();
                return view('admin.reports.revenue', compact('events', 'selectedEvent', 'totalInvoiced', 'totalPaid', 'totalBalance', 'bookings'));

            case 'hotels':
                $hotels = Hotel::where('event_id', $eventId)->get();
                $sleepers = \App\Models\Bookers::where('event_id', $eventId)
                    ->where('booking_status', 'Confirmed')
                    ->where('accommodation', true)
                    ->whereNotNull('hotel_id')
                    ->with('hotel')
                    ->select('name', 'email', 'company', 'hotel_id', 'room_number')
                    ->limit(100)
                    ->get();
                return view('admin.reports.hotels', compact('events', 'selectedEvent', 'hotels', 'sleepers'));

            case 'meals':
                $totalCoupons = DB::table('meal_coupon')->where('event_id', $eventId)->count();
                $totalOffered = DB::table('meal_coupon')->where('event_id', $eventId)->sum('total_meals');
                $totalRedeemed = DB::table('meal_coupon')->where('event_id', $eventId)
                    ->whereNotNull('meals_redeemed')->where('meals_redeemed', '!=', '')
                    ->sum(DB::raw('CAST(meals_redeemed AS UNSIGNED)'));

                // Master meal tag breakdown
                $masterCoupons = DB::table('master_meal_tags')->where('event_id', $eventId)->count();
                $masterMeals = DB::table('master_meal_tags')->where('event_id', $eventId)->sum('total_meals');
                $masterRedeemed = DB::table('meal_coupon')
                    ->where('event_id', $eventId)
                    ->whereIn('unique_code', function ($q) use ($eventId) {
                        $q->select('unique_code')->from('master_meal_tags')->where('event_id', $eventId);
                    })
                    ->sum(DB::raw('CAST(meals_redeemed AS UNSIGNED)'));

                $mealScans = DB::table('meal_scans_per_day')
                    ->where('event_id', $eventId)
                    ->select(
                        'date',
                        'hotel_name',
                        'time',
                        DB::raw('COUNT(*) as scan_count')
                    )
                    ->groupBy('date', 'hotel_name', 'time')
                    ->orderBy('date')
                    ->orderBy('hotel_name')
                    ->get()
                    ->map(function ($item) {
                        $hour = $item->time ? (int)substr($item->time, 0, 2) : 12;
                        $item->meal_period = $hour < 14 ? 'Lunch' : 'Supper';
                        $item->day_number = 'Day ' . ($item->date ?: '?');
                        return $item;
                    });

                return view('admin.reports.meals', compact('events', 'selectedEvent', 'totalCoupons', 'totalOffered', 'totalRedeemed', 'masterCoupons', 'masterMeals', 'masterRedeemed', 'mealScans'));

            case 'attendance':
                $sessions = DB::table('event_sessions')->where('event_id', $eventId)->get();

                $attendanceData = [];
                foreach ($sessions as $session) {
                    $total = DB::table('attendance_registration')
                        ->where('session_id', $session->session_id)->count();

                    $members = DB::table('attendance_registration')
                        ->join('event_participants', 'attendance_registration.reference_code', '=', 'event_participants.reference_code')
                        ->where('attendance_registration.session_id', $session->session_id)
                        ->where('event_participants.status', 'Member')
                        ->count();

                    $nonMembers = DB::table('attendance_registration')
                        ->join('event_participants', 'attendance_registration.reference_code', '=', 'event_participants.reference_code')
                        ->where('attendance_registration.session_id', $session->session_id)
                        ->where('event_participants.status', '!=', 'Member')
                        ->count();

                    $attendanceData[] = (object)[
                        'session_date' => $session->session_date,
                        'description' => $session->description,
                        'total' => $total,
                        'members' => $members,
                        'non_members' => $nonMembers,
                    ];
                }

                $data = collect($attendanceData);
                return view('admin.reports.attendance', compact('events', 'selectedEvent', 'data'));

            // ======= EVALUATION REPORTS =======
            case 'evaluation-participation':
                $confirmed = Participant::where('event_id', $eventId)->count();
                $submissions = DB::table('evaluation_submissions')->where('event_id', $eventId)->count();
                $rate = $confirmed > 0 ? round(($submissions / $confirmed) * 100, 1) : 0;
                $participants = Participant::where('event_id', $eventId)
                    ->select('reference_code', 'participant', 'email_address', 'company_name')
                    ->get();
                $submittedRefs = DB::table('evaluation_submissions')->where('event_id', $eventId)->pluck('reference_code')->toArray();
                return view('admin.reports.evaluation-participation', compact('events', 'selectedEvent', 'confirmed', 'submissions', 'rate', 'participants', 'submittedRefs'));

            case 'evaluation-scores':
                $questions = DB::table('participant_evaluation')
                    ->where('event_id', $eventId)
                    ->where('type', 'radio')
                    ->get();
                $submissions = DB::table('evaluation_submissions')->where('event_id', $eventId)->get();
                $scores = [];
                foreach ($questions as $q) {
                    $answers = [];
                    foreach ($submissions as $s) {
                        $ans = json_decode($s->answers, true);
                        if (is_string($ans)) $ans = json_decode($ans, true);
                        $val = $ans[$q->id] ?? null;
                        if ($val) $answers[] = $val;
                    }
                    $counts = array_count_values($answers);
                    $avg = count($answers) > 0 ? round(array_sum($answers) / count($answers), 2) : 0;
                    $scores[] = (object)[
                        'id' => $q->id,
                        'question' => $q->questions,
                        'section' => $q->section,
                        'avg' => $avg,
                        'counts' => $counts,
                        'total' => count($answers),
                    ];
                }
                return view('admin.reports.evaluation-scores', compact('events', 'selectedEvent', 'scores'));

            case 'evaluation-speakers':
                $speakers = DB::table('speakers')->where('event_id', $eventId)->get();
                $ratings = DB::table('speaker_ratings')->where('event_id', $eventId)->get();
                $speakerData = [];
                foreach ($speakers as $sp) {
                    $spRatings = $ratings->where('speaker_id', $sp->id);
                    $scoresList = $spRatings->pluck('rating')->toArray();
                    $avg = count($scoresList) > 0 ? round(array_sum($scoresList) / count($scoresList), 2) : 0;
                    $distribution = array_count_values($scoresList);
                    $comments = $spRatings->whereNotNull('comment')->pluck('comment')->toArray();
                    $speakerData[] = (object)[
                        'name' => $sp->name,
                        'title' => $sp->title,
                        'avg' => $avg,
                        'total' => count($scoresList),
                        'distribution' => $distribution,
                        'comments' => $comments,
                    ];
                }
                usort($speakerData, fn($a, $b) => $b->avg <=> $a->avg);
                return view('admin.reports.evaluation-speakers', compact('events', 'selectedEvent', 'speakerData'));

            case 'evaluation-feedback':
                $questions = DB::table('participant_evaluation')
                    ->where('event_id', $eventId)
                    ->where('type', 'text')
                    ->get();
                $submissions = DB::table('evaluation_submissions')->where('event_id', $eventId)->get();
                $feedbackData = [];
                foreach ($questions as $q) {
                    $responses = [];
                    foreach ($submissions as $s) {
                        $ans = json_decode($s->answers, true);
                        if (is_string($ans)) $ans = json_decode($ans, true);
                        $val = $ans[$q->id] ?? null;
                        if (!empty($val)) {
                            $participant = Participant::where('reference_code', $s->reference_code)->first();
                            $responses[] = (object)[
                                'participant' => optional($participant)->participant ?? $s->reference_code,
                                'company' => optional($participant)->company_name ?? '',
                                'answer' => $val,
                            ];
                        }
                    }
                    $feedbackData[] = (object)[
                        'question' => $q->questions,
                        'section' => $q->section,
                        'responses' => $responses,
                    ];
                }
                return view('admin.reports.evaluation-feedback', compact('events', 'selectedEvent', 'feedbackData'));

            case 'evaluation-individual':
                $submissions = DB::table('evaluation_submissions')->where('event_id', $eventId)->get();
                $questions = DB::table('participant_evaluation')->where('event_id', $eventId)->get();
                $individualData = [];
                foreach ($submissions as $s) {
                    $participant = Participant::where('reference_code', $s->reference_code)->first();
                    $raw = json_decode($s->answers, true);
                    if (is_string($raw)) $raw = json_decode($raw, true);
                    $answers = $raw ?? [];
                    $individualData[] = (object)[
                        'reference_code' => $s->reference_code,
                        'participant' => optional($participant)->participant ?? $s->reference_code,
                        'company' => optional($participant)->company_name ?? '',
                        'answers' => $answers,
                    ];
                }
                return view('admin.reports.evaluation-individual', compact('events', 'selectedEvent', 'questions', 'individualData'));

            case 'evaluation-certificates':
                $confirmed = Participant::where('event_id', $eventId)
                    ->whereNotIn('status', ['Cancelled', 'Declined'])
                    ->select('reference_code', 'participant', 'email_address', 'company_name')
                    ->get();

                $submittedRefs = DB::table('evaluation_submissions')
                    ->where('event_id', $eventId)
                    ->pluck('reference_code')
                    ->unique()
                    ->toArray();

                $event = $selectedEvent;
                return view('admin.reports.evaluation-certificates', compact('events', 'selectedEvent', 'confirmed', 'submittedRefs', 'event'));

            default:
                abort(404);
        }

        return view('admin.reports.show', compact('events', 'selectedEvent', 'data', 'type'));
    }
}
