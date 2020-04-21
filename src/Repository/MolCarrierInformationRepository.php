<?php

namespace Mollie\Repository;

use Db;
use DbQuery;

class MolCarrierInformationRepository
{
    public function getMollieCarrierInformationIdByCarrierId($carrierId)
    {
        $sql = new DbQuery();
        $sql->select('id_mol_carrier_information');
        $sql->from('mol_carrier_information');
        $sql->where('`id_carrier` = ' . (int)$carrierId . '');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }
}