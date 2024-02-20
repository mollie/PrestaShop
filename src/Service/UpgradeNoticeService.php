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

namespace Mollie\Service;

use Mollie\Config\Config;
use Mollie\Utility\TimeUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class UpgradeNoticeService
{
    /**
     * @param int $currentTimeStamp
     * @param int $noticeCloseTimeStamp
     *
     * @return bool
     */
    public function isUpgradeNoticeClosed($currentTimeStamp, $noticeCloseTimeStamp)
    {
        $closeDuration = TimeUtility::getDayMeasuredInSeconds(Config::MODULE_UPGRADE_NOTICE_CLOSE_DURATION);
        if ($noticeCloseTimeStamp + $closeDuration >= $currentTimeStamp) {
            return true;
        }

        return false;
    }
}
