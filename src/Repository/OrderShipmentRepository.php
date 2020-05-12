<?php

namespace Mollie\Repository;

use Db;
use DbQuery;

class OrderShipmentRepository
{
    public function getShipmentInformation($table, $orderId)
    {
        $sql = new DbQuery();
        $sql->select('`tracktrace`, `postcode`');
        $sql->from(bqSQL($table));
        $sql->where('`id_order` = "' . pSQL($orderId) . '"');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
    }
}