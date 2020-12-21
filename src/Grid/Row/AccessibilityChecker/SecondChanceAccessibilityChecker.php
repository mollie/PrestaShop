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
 */

namespace Mollie\Grid\Row\AccessibilityChecker;

use PrestaShop\PrestaShop\Core\Grid\Action\Row\AccessibilityChecker\AccessibilityCheckerInterface;

/**
 * Checks if second chance email option can be visible in order list.
 */
final class SecondChanceAccessibilityChecker implements AccessibilityCheckerInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function isGranted(array $record)
	{
		return !empty($record['transaction_id']);
	}
}
