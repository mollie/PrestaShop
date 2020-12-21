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

namespace Mollie\Utility;

use Context;
use PrestaShopDatabaseException;
use PrestaShopException;
use Tab;
use Validate;

class MenuLocationUtility
{
	/**
	 * Get page location.
	 *
	 * @param string $class
	 * @param int|null $idLang
	 *
	 * @return string
	 *
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 *
	 * @since 3.3.2
	 */
	public static function getMenuLocation($class, $idLang = null)
	{
		if (!$idLang) {
			$idLang = Context::getContext()->language->id;
		}

		return implode(' > ', array_reverse(array_unique(array_map(function ($tab) use ($idLang) {
			return $tab->name[$idLang];
		}, static::getTabTreeByClass($class)))));
	}

	/**
	 * Get the entire tab tree by tab class name.
	 *
	 * @param string $class
	 *
	 * @return Tab[]|null
	 *
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 *
	 * @since 3.3.2
	 */
	public static function getTabTreeByClass($class)
	{
		$tabs = [];
		$depth = 10;
		$tab = Tab::getInstanceFromClassName($class);
		while (Validate::isLoadedObject($tab) && $depth > 0) {
			--$depth;
			$tabs[] = $tab;
			$tab = new Tab($tab->id_parent);
		}

		return $tabs;
	}
}
