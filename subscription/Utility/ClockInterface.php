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

namespace Mollie\Subscription\Utility;

if (!defined('_PS_VERSION_')) {
    exit;
}

interface ClockInterface
{
    public function getCurrentDate(string $format = 'Y-m-d H:i:s'): string;

    public function getDateFromTimeStamp(int $timestamp, string $format = 'Y-m-d H:i:s'): string;
}
