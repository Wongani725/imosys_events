<?php

namespace App\Helpers;

class MealCalculator
{
    /**
     * Calculate total meals based on event type, accommodation, and event selection.
     *
     * @param string $eventType  'governance' or 'main'
     * @param bool   $hasAccommodation
     * @param bool   $isBoth     Whether attending both events
     * @return int   Total number of meals
     */
    public static function calculate($eventType, $hasAccommodation, $isBoth = false)
    {
        if ($isBoth && $hasAccommodation) {
            return $eventType === 'governance' ? 6 : 5;
        }

        if ($eventType === 'governance') {
            return $hasAccommodation ? 5 : 2;
        }

        // main event
        return $hasAccommodation ? 5 : 2;
    }

    /**
     * Get breakdown of meals per day
     */
    public static function breakdown($eventType, $hasAccommodation, $isBoth = false)
    {
        if ($isBoth && $hasAccommodation) {
            if ($eventType === 'governance') {
                return [
                    'Sep 7 Dinner' => 1,
                    'Sep 8 Lunch + Dinner' => 2,
                    'Sep 9 Lunch + Dinner' => 2,
                    'Sep 10 Lunch' => 1,
                    'total' => 6,
                ];
            }
            return [
                'Sep 10 Dinner' => 1,
                'Sep 11 Lunch + Dinner' => 2,
                'Sep 12 Lunch + Dinner' => 2,
                'total' => 5,
            ];
        }

        if ($eventType === 'governance') {
            if ($hasAccommodation) {
                return [
                    'Sep 7 Dinner' => 1,
                    'Sep 8 Lunch + Dinner' => 2,
                    'Sep 9 Lunch + Dinner' => 2,
                    'total' => 5,
                ];
            }
            return [
                'Sep 8 Lunch' => 1,
                'Sep 9 Lunch' => 1,
                'total' => 2,
            ];
        }

        // main event
        if ($hasAccommodation) {
            return [
                'Sep 10 Dinner' => 1,
                'Sep 11 Lunch + Dinner' => 2,
                'Sep 12 Lunch + Dinner' => 2,
                'total' => 5,
            ];
        }
        return [
            'Sep 11 Lunch' => 1,
            'Sep 12 Lunch' => 1,
            'total' => 2,
        ];
    }
}
