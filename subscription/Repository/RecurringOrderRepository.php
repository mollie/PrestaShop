<?php

declare(strict_types=1);

namespace Mollie\Subscription\Repository;

use MolRecurringOrder;
use PrestaShop\PrestaShop\Adapter\Entity\Db;
use PrestaShop\PrestaShop\Adapter\Entity\DbQuery;
use PrestaShopCollection;

class RecurringOrderRepository extends AbstractRepository implements RecurringOrderRepositoryInterface
{
    public function findOneBy(array $keyValueCriteria): ?MolRecurringOrder
    {
        /** @var ?MolRecurringOrder $result */
        $result = parent::findOneBy($keyValueCriteria);

        return $result;
    }

    public function findAllBy(array $keyValueCriteria): ?PrestaShopCollection
    {
        /** @var ?PrestaShopCollection $result */
        $result = parent::findAllBy($keyValueCriteria);

        return $result;
    }

    public function getCustomerOrdersByOldAddress(int $customerId, int $oldAddressId): ?PrestaShopCollection
    {
        $sql = new DbQuery();

        $sql->select('o.id_order, o.id_address_delivery, o.id_address_invoice');
        $sql->from('mol_recurring_order', 'mro');
        $sql->leftJoin('orders', 'o', 'mro.id_order = o.id_order');
        $sql->where('mro.id_customer = ' . $customerId);
        $sql->where('o.id_address_delivery = ' . $oldAddressId . ' OR o.id_address_invoice = ' . $oldAddressId);

        $result =  Db::getInstance()->executeS($sql);

        if (is_array($result) && !empty($result)) {
            return $result;
        }

        return null;
    }
}
