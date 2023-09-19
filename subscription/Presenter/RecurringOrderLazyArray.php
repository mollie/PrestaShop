<?php

namespace Mollie\Subscription\Presenter;

use PrestaShop\PrestaShop\Adapter\Presenter\Order\OrderLazyArray;

class RecurringOrderLazyArray extends OrderLazyArray
{
    /** @var int */
    private $recurringOrderProductAttributeId;

    public function setRecurringOrderProductAttributeId(int $recurringOrderProductAttributeId): void
    {
        $this->recurringOrderProductAttributeId = $recurringOrderProductAttributeId;
    }

    /**
     * @arrayAccess
     *
     * @return array
     */
    public function getProducts(): array
    {
        $subscriptionProduct = [];

        $orderProducts = parent::getProducts();

        foreach ($orderProducts as $orderProduct) {
            if ((int) $orderProduct['id_product_attribute'] !== $this->recurringOrderProductAttributeId) {
                continue;
            }

            $subscriptionProduct[] = $orderProduct;

            break;
        }

        return $subscriptionProduct;
    }
}
