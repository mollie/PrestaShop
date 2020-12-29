<?php
/**
 * 2007-2017 PrestaShop.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

namespace Mollie\Install;

use Db;

final class DatabaseTableUninstaller implements UninstallerInterface
{
	public function uninstall()
	{
		foreach ($this->getCommands() as $query) {
			if (false == Db::getInstance()->execute($query)) {
				return false;
			}
		}

		return true;
	}

	private function getCommands()
	{
		$sql = [];

		$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mol_country`;';
		$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mol_payment_method`;';
		$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mol_payment_method_issuer`;';
		$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mol_order_fee`;';
		$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mol_carrier_information`;';
		$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mol_pending_order_cart`;';
		$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mol_excluded_country`;';
		$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mol_pending_order_cart_rule`;';
		$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mol_payment_method_order_total_restriction`;';

		return $sql;
	}
}
