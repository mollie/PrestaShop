<?php

namespace Mollie\Service;

use Mollie\Config\Config;

class UpgradeNoticeService
{
    public function isUpgradeNoticeClosed($timeStamp)
    {
        $closedTimeStamp = \Configuration::get(Config::MODULE_UPGRADE_NOTICE_CLOSE_DATE);
        $closeDuration = strtotime(Config::MODULE_UPGRADE_NOTICE_CLOSE_DURATION) - time();
        if ($closedTimeStamp + $closeDuration > $timeStamp) {
            return true;
        }

        return false;
    }
}
