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

use Country;
use Db;

final class CountryRepository extends AbstractRepository
{
	public function __construct()
	{
		parent::__construct(Country::class);
	}

	public function getMethodCountryIds($methodId)
	{
		$sql = 'SELECT id_country FROM `' . _DB_PREFIX_ . 'mol_country` WHERE id_method = "' . pSQL($methodId) . '"';

		$countryIds = Db::getInstance()->executeS($sql);
		$countryIdsArray = [];
		foreach ($countryIds as $countryId) {
			$countryIdsArray[] = $countryId['id_country'];
		}

		return $countryIdsArray;
	}

	public function updatePaymentMethodCountries($idMethod, $idCountries)
	{
		$sql = 'DELETE FROM ' . _DB_PREFIX_ . 'mol_country WHERE `id_method` = "' . $idMethod . '"';
		if (!Db::getInstance()->execute($sql)) {
			return false;
		}

		if (false == $idCountries) {
			return true;
		}

		$response = true;
		foreach ($idCountries as $idCountry) {
			$allCountries = 0;
			$sql = 'INSERT INTO `' . _DB_PREFIX_ . 'mol_country` (id_method, id_country, all_countries) VALUES (';

			if ('0' === $idCountry) {
				$allCountries = 1;
			}
			$sql .= '"' . pSQL($idMethod) . '", ' . (int) $idCountry . ', ' . (int) $allCountries . ')';

			if (!Db::getInstance()->execute($sql)) {
				$response = false;
			}
		}

		return $response;
	}

	public function getExcludedCountryIds($methodId)
	{
		$sql = 'SELECT id_country
                    FROM `' . _DB_PREFIX_ . 'mol_excluded_country`
                    WHERE id_method = "' . pSQL($methodId) . '"';

		$countryIds = Db::getInstance()->executeS($sql);
		$countryIdsArray = [];
		foreach ($countryIds as $countryId) {
			$countryIdsArray[] = $countryId['id_country'];
		}

		return $countryIdsArray;
	}

	public function updatePaymentMethodExcludedCountries($idMethod, $idCountries)
	{
		$sql = 'DELETE FROM ' . _DB_PREFIX_ . 'mol_excluded_country WHERE `id_method` = "' . $idMethod . '"';
		if (!Db::getInstance()->execute($sql)) {
			return false;
		}

		if (false == $idCountries) {
			return true;
		}

		$response = true;
		foreach ($idCountries as $idCountry) {
			$allCountries = 0;
			$sql = 'INSERT INTO `' . _DB_PREFIX_ . 'mol_excluded_country` (id_method, id_country, all_countries)
                VALUES (';

			if ('0' === $idCountry) {
				$allCountries = 1;
			}
			$sql .= '"' . pSQL($idMethod) . '", ' . (int) $idCountry . ', ' . (int) $allCountries . ')';

			if (!Db::getInstance()->execute($sql)) {
				$response = false;
			}
		}

		return $response;
	}
}
