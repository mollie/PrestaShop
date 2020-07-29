<?php

namespace Mollie\Repository;

use Db;
use DbQuery;

final class ShippedProductRepository extends AbstractRepository
{
    public function getProductsWithoutInvoiceByOrderId($orderId)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('mol_shipped_product', 'sp');
        $sql->where('`order_id` = "' . (int)$orderId . '"');
        $sql->innerJoin('mol_klarna_invoice', 'kl', 'kl.shipment_id = sp.shipment_id AND kl.is_created = 0');

        return Db::getInstance()->executeS($sql);
    }
}