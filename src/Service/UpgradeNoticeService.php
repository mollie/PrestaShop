<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Service;

use Mollie\Config\Config;
use Mollie\Utility\TimeUtility;

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
