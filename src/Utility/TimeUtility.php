<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Utility;

use DateTime;

if (!defined('_PS_VERSION_')) {
    exit;
}

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
     * @param int $days
     *
     * @return float|int
     */
    public static function getDayMeasuredInSeconds($days)
    {
        return $days * self::HOURS_IN_DAY * self::MINUTES_IN_HOUR * self::SECONDS_IN_MINUTE;
    }

    public static function getCurrentTimeStamp()
    {
        return (new DateTime())->getTimestamp();
    }
}
