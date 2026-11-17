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

namespace Mollie\Repository;

use Db;
use DbQuery;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ModuleRestrictionRepository implements ModuleRestrictionRepositoryInterface
{
    /**
     * -1 ("customer currency") and -2 ("shop default currency") accept any currency, and core
     * ignores module_currency.id_shop when it lists payment options.
     */
    public function isCurrencyAllowed(int $moduleId, int $shopId, int $currencyId): bool
    {
        $query = new DbQuery();
        $query->select('1');
        $query->from('module_currency');
        $query->where('`id_module` = ' . $moduleId);
        $query->where('`id_currency` IN (' . $currencyId . ', -1, -2)');

        return $this->exists($query);
    }

    public function isCountryAllowed(int $moduleId, int $shopId, int $countryId): bool
    {
        $query = new DbQuery();
        $query->select('1');
        $query->from('module_country');
        $query->where('`id_module` = ' . $moduleId);
        $query->where('`id_shop` = ' . $shopId);
        $query->where('`id_country` = ' . $countryId);

        return $this->exists($query);
    }

    public function isCarrierReferenceAllowed(int $moduleId, int $shopId, int $carrierReference): bool
    {
        $query = new DbQuery();
        $query->select('1');
        $query->from('module_carrier');
        $query->where('`id_module` = ' . $moduleId);
        $query->where('`id_shop` = ' . $shopId);
        $query->where('`id_reference` = ' . $carrierReference);

        return $this->exists($query);
    }

    public function isAnyGroupAllowed(int $moduleId, int $shopId, array $groupIds): bool
    {
        $groupIds = array_map('intval', $groupIds);

        if (!$groupIds) {
            return false;
        }

        $query = new DbQuery();
        $query->select('1');
        $query->from('module_group');
        $query->where('`id_module` = ' . $moduleId);
        $query->where('`id_shop` = ' . $shopId);
        $query->where('`id_group` IN (' . implode(', ', $groupIds) . ')');

        return $this->exists($query);
    }

    private function exists(DbQuery $query): bool
    {
        return (bool) Db::getInstance()->getValue($query);
    }
}
