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

/**
 * Repository related with payment methods country.
 */
class MethodCountryRepository
{
    public function checkIfMethodIsAvailableInCountry($methodId, $countryId): bool
    {
        $sql = new DbQuery();
        $sql->select('`id_mol_country`');
        $sql->from('mol_country');
        $sql->where('`id_method` = "' . (int) $methodId . '" AND ( id_country = ' . (int) $countryId . ' OR all_countries = 1)');

        $result = Db::getInstance()->getValue($sql);

        return !empty($result);
    }

    public function checkIfCountryIsExcluded($methodId, $countryId): bool
    {
        $sql = new DbQuery();
        $sql->select('`id_mol_country`');
        $sql->from('mol_excluded_country');
        $sql->where('`id_method` = "' . (int) $methodId . '" AND ( id_country = ' . (int) $countryId . ' OR all_countries = 1)');

        $result = Db::getInstance()->getValue($sql);

        return is_numeric($result) && $result > 0;
    }
}
