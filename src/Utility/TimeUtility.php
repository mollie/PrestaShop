<?php

namespace Mollie\Utility;

class TimeUtility
{
    const HOURS_IN_DAY = 24;
    const MINUTES_IN_HOUR = 60;
    const SECONDS_IN_MINUTE = 60;

    public static function getNowTs()
    {
        return time();
    }

    /**
     * @param $days int
     */
    public static function getDayMeasuredInSeconds($days)
    {
        return $days * self::HOURS_IN_DAY * self::MINUTES_IN_HOUR * self::SECONDS_IN_MINUTE;
    }
}
