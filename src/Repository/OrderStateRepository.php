<?php

namespace Mollie\Repository;

use Db;
use Mollie\Config\Config;

class OrderStateRepository
{
    public function deleteStatuses() {
        $sql = 'UPDATE '. _DB_PREFIX_ . 'order_state SET deleted = 1 WHERE module_name = "' . Config::NAME . '"';

        return Db::getInstance()->execute($sql);
    }
}
