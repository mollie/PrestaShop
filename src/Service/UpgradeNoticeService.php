<?php

namespace Mollie\Service;

use Mollie\Config\Config;
use Mollie\Utility\TimeUtility;

class UpgradeNoticeService
{
    /**
     * @param $currentTimeStamp int
     * @param $noticeCloseTimeStamp int
     * @return bool
     */
    public function isUpgradeNoticeClosed($currentTimeStamp, $noticeCloseTimeStamp)
    {
        $closeDuration = TimeUtility::getDayMeasuredInSeconds( Config::MODULE_UPGRADE_NOTICE_CLOSE_DURATION);
        if ($noticeCloseTimeStamp + $closeDuration >= $currentTimeStamp) {
            return true;
        }

        return false;
    }
}
