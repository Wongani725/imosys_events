<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Bookers;
use App\Models\Member;
use App\Models\Participant;
use App\Models\Hotel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $event = Event::where('event_status', 'active')->orderBy('start_date', 'desc')->first();
        if (!$event) $event = Event::latest()->first();
        $eventId = $event->event_id;
        $events = Event::orderBy('start_date', 'desc')->get();

        $data = $this->buildDashboardData($eventId);
        $data['eventName'] = $event->event_name;
        $data['event'] = $event;
        $data['events'] = $events;

        return view('event_dashboard.dashboard', $data);
    }

    public function showDashboard(Request $request)
    {
        $eventId = $request->event_id;
        $event = Event::where('event_id', $eventId)->firstOrFail();
        $events = Event::orderBy('start_date', 'desc')->get();

        $data = $this->buildDashboardData($eventId);
        $data['eventName'] = $event->event_name;
        $data['event'] = $event;
        $data['events'] = $events;

        return view('event_dashboard.dashboard', $data);
    }

    private function buildDashboardData($eventId)
    {
        // Bookers summary
        $totalBookers = Bookers::where('event_id', $eventId)->count();
        $pendingPayment = Bookers::where('event_id', $eventId)->where('booking_status', 'Pending Payment')->count();
        $confirmedBookers = Bookers::where('event_id', $eventId)->where('booking_status', 'Confirmed')->count();
        $declinedBookers = Bookers::where('event_id', $eventId)->where('booking_status', 'Declined')->count();
        $cancelledBookers = Bookers::where('event_id', $eventId)->where('booking_status', 'Cancelled')->count();

        // Revenue (exclude cancelled/declined)
        $revenueBase = Bookers::where('event_id', $eventId)->whereNotIn('booking_status', ['Cancelled', 'Declined']);
        $totalInvoiced = (clone $revenueBase)->sum('total_cost');
        $totalPaid = (clone $revenueBase)->sum('amount_paid');
        $outstandingBalance = (clone $revenueBase)->sum('balance');

        // Participants (initial registration at event — from event_participants)
        $totalParticipants = Participant::where('event_id', $eventId)->count();
        $memberParticipants = Participant::where('event_id', $eventId)->where('status', 'Member')->count();
        $nonMemberParticipants = Participant::where('event_id', $eventId)->where('status', 'Non member')->count();
        $walkinParticipants = Participant::where('event_id', $eventId)->where('is_walkin', true)->count();

        // Hotels occupancy
        $hotels = Hotel::where('event_id', $eventId)->get();
        $totalRooms = $hotels->sum('quantity');
        $bookedRooms = $hotels->sum('booked_count');
        $availableRooms = $hotels->sum('available_count');

        // Meal coupons
        $totalMealCoupons = DB::table('meal_coupon')->where('event_id', $eventId)->count();
        $totalMealsOffered = DB::table('meal_coupon')->where('event_id', $eventId)->sum('total_meals');
        $totalMealsRedeemed = DB::table('meal_coupon')->where('event_id', $eventId)
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

        // Hotel meals redeemed (from scanning logs)
        $hotelMealsRedeemed = DB::table('meal_scans_per_day')
            ->select(
                'hotel_name',
                DB::raw('SUM(CASE WHEN participant_reference_code = unique_code THEN 1 ELSE 0 END) as premium_scans'),
                DB::raw('SUM(CASE WHEN participant_reference_code != unique_code THEN 1 ELSE 0 END) as extras_scans'),
                DB::raw('COUNT(*) as total_scans')
            )
            ->where('event_id', $eventId)
            ->groupBy('hotel_name')
            ->get();

        // Session attendance by day / period (Morning / Afternoon)
        $sessionAttendance = DB::table('event_sessions')
            ->leftJoin('attendance_registration', 'attendance_registration.session_id', '=', 'event_sessions.session_id')
            ->where('event_sessions.event_id', $eventId)
            ->select(
                'event_sessions.session_date',
                'event_sessions.description',
                DB::raw('COUNT(attendance_registration.id) as attendee_count')
            )
            ->groupBy('event_sessions.session_date', 'event_sessions.description')
            ->orderBy('event_sessions.session_date')
            ->orderBy('event_sessions.description')
            ->get();

        return compact(
            'totalBookers', 'pendingPayment', 'confirmedBookers', 'declinedBookers', 'cancelledBookers',
            'totalInvoiced', 'totalPaid', 'outstandingBalance',
            'totalParticipants', 'memberParticipants', 'nonMemberParticipants', 'walkinParticipants',
            'hotels', 'totalRooms', 'bookedRooms', 'availableRooms',
            'totalMealCoupons', 'totalMealsOffered', 'totalMealsRedeemed',
            'masterCoupons', 'masterMeals', 'masterRedeemed',
            'hotelMealsRedeemed', 'sessionAttendance'
        );
    }
}
