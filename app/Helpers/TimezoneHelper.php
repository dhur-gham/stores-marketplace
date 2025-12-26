<?php

namespace App\Helpers;

use Carbon\Carbon;

class TimezoneHelper
{
    /**
     * Baghdad timezone
     */
    private const BAGHDAD_TIMEZONE = 'Asia/Baghdad';

    /**
     * Convert Baghdad datetime to UTC for database storage
     *
     * @param  string|Carbon  $datetime  Datetime in Baghdad timezone (format: Y-m-d H:i:s or Carbon instance)
     * @return Carbon UTC datetime
     */
    public static function baghdadToUtc(string|Carbon $datetime): Carbon
    {
        if (is_string($datetime)) {
            $datetime = Carbon::createFromFormat('Y-m-d H:i:s', $datetime, self::BAGHDAD_TIMEZONE);
        } else {
            $datetime = $datetime->setTimezone(self::BAGHDAD_TIMEZONE);
        }

        return $datetime->setTimezone('UTC');
    }

    /**
     * Convert UTC datetime to Baghdad timezone for display
     *
     * @param  string|Carbon  $datetime  Datetime in UTC (format: Y-m-d H:i:s or Carbon instance)
     * @return Carbon Baghdad datetime
     */
    public static function utcToBaghdad(string|Carbon $datetime): Carbon
    {
        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime, 'UTC');
        } else {
            $datetime = $datetime->setTimezone('UTC');
        }

        return $datetime->setTimezone(self::BAGHDAD_TIMEZONE);
    }

    /**
     * Format datetime in Baghdad timezone for display
     *
     * @param  string|Carbon  $datetime  Datetime in UTC
     * @param  string  $format  Format string
     * @return string Formatted datetime string
     */
    public static function formatBaghdad(string|Carbon $datetime, string $format = 'Y-m-d H:i:s'): string
    {
        return self::utcToBaghdad($datetime)->format($format);
    }
}

