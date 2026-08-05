<?php

namespace App\Support;

use Carbon\Carbon;

class TimelineFormatter
{
    /**
     * Format a duration between two timestamps into a clean, human-readable string.
     * Never produces raw floats or decimals.
     *
     * @param  Carbon|string  $start
     * @param  Carbon|string|null  $end  Defaults to now()
     * @return string
     */
    public static function format($start, $end = null): string
    {
        $start = $start instanceof Carbon ? $start : Carbon::parse($start);
        $end   = $end ? ($end instanceof Carbon ? $end : Carbon::parse($end)) : Carbon::now();

        $totalMinutes = (int) $start->diffInMinutes($end, false);
        if ($totalMinutes < 0) {
            $totalMinutes = 0;
        }

        $totalHours = intdiv($totalMinutes, 60);
        $remainingMinutes = $totalMinutes % 60;

        if ($totalHours < 24) {
            // Under 24 hours → "Xh Ym"
            return $totalHours . 'h ' . $remainingMinutes . 'm';
        }

        // 24 hours or more → "X days Y hrs"
        $days = intdiv($totalHours, 24);
        $remainingHours = $totalHours % 24;

        $dayLabel = $days === 1 ? 'day' : 'days';
        $hrLabel  = $remainingHours === 1 ? 'hr' : 'hrs';

        if ($remainingHours === 0) {
            return $days . ' ' . $dayLabel;
        }

        return $days . ' ' . $dayLabel . ' ' . $remainingHours . ' ' . $hrLabel;
    }
}
