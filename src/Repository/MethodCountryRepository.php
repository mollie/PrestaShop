<?php

namespace Mollie\Repository;

use Db;
use DbQuery;

class MethodCountryRepository
{

    public function checkIfMethodIsAvailableInCountry($methodId, $countryId)
    {
        $sql = new DbQuery();
        $sql->select('`id_mol_country`');
        $sql->from('mol_country');
        $sql->where('`id_method` = "' . pSQL($methodId) . '" AND ( id_country = ' . (int)$countryId . ' OR all_countries = 1)');

        return Db::getInstance()->getValue($sql);
    }
}