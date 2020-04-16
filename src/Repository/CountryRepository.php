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
}