<?php

namespace Mollie\Repository;

use Db;

class CountryRepository
{
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

        if ($idCountries == false) {
            return true;
        }

        $response = true;
        foreach ($idCountries as $idCountry) {
            $allCountries = 0;
            $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'mol_country` (id_method, id_country, all_countries) VALUES (';

            if ($idCountry === '0') {
                $allCountries = 1;
            }
            $sql .= '"' . pSQL($idMethod) . '", ' . (int)$idCountry . ', ' . (int)$allCountries . ')';

            if (!Db::getInstance()->execute($sql)) {
                $response = false;
            }
        }

        return $response;
    }
}