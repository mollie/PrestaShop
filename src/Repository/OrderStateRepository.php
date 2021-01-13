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

namespace Mollie\Repository;

use Db;
use Mollie\Config\Config;

class OrderStateRepository
{
	public function deleteStatuses()
	{
		$sql = 'UPDATE ' . _DB_PREFIX_ . 'order_state SET deleted = 1 WHERE module_name = "' . Config::NAME . '"';

		return Db::getInstance()->execute($sql);
	}
}
