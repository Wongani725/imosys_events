<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Check if a participant has attended >= threshold% of event sessions.
     */
    public static function isEligibleForEvaluation($referenceCode, $eventId, $threshold = 70): bool
    {
        return self::getAttendancePercentage($referenceCode, $eventId) >= $threshold;
    }

    /**
     * Get the percentage of sessions a participant attended.
     */
    public static function getAttendancePercentage($referenceCode, $eventId): float
    {
        $totalSessions = DB::table('event_sessions')->where('event_id', $eventId)->count();
        if ($totalSessions <= 0) return 0;

        $attended = DB::table('attendance_registration')
            ->join('event_sessions', 'attendance_registration.session_id', '=', 'event_sessions.session_id')
            ->where('attendance_registration.reference_code', $referenceCode)
            ->where('event_sessions.event_id', $eventId)
            ->distinct('attendance_registration.session_id')
            ->count('attendance_registration.session_id');

        return round(($attended / $totalSessions) * 100, 2);
    }

    /**
     * Get session attendance breakdown for a participant.
     */
    public static function getAttendanceBreakdown($referenceCode, $eventId): array
    {
        $totalSessions = DB::table('event_sessions')->where('event_id', $eventId)->count();
        if ($totalSessions <= 0) return ['total_sessions' => 0, 'attended' => 0, 'percentage' => 0];

        $attended = DB::table('attendance_registration')
            ->join('event_sessions', 'attendance_registration.session_id', '=', 'event_sessions.session_id')
            ->where('attendance_registration.reference_code', $referenceCode)
            ->where('event_sessions.event_id', $eventId)
            ->distinct('attendance_registration.session_id')
            ->count('attendance_registration.session_id');

        return [
            'total_sessions' => $totalSessions,
            'attended' => $attended,
            'percentage' => round(($attended / $totalSessions) * 100, 2),
        ];
    }
}
