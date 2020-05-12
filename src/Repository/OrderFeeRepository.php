<?php

namespace Mollie\Repository;

use Db;

class OrderFeeRepository
{
    public function getOrderFeeIdByCartId($cartId)
    {
        $sql = 'Select id_mol_order_fee FROM `' . _DB_PREFIX_ . 'mol_order_fee` WHERE id_cart = "' . (int)$cartId . '"';

        return Db::getInstance()->getValue($sql);
    }
}