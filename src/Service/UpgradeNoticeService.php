<?php

namespace Mollie\Service;

use Mollie\Config\Config;

class UpgradeNoticeService
{
    public function isUpgradeNoticeClosed($currentTimeStamp, $noticeCloseTimeStamp)
    {
        $closeDuration = strtotime(Config::MODULE_UPGRADE_NOTICE_CLOSE_DURATION) - $currentTimeStamp;
        if ($noticeCloseTimeStamp + $closeDuration > $currentTimeStamp) {
            return true;
        }

        return false;
    }
}
