<?php

namespace Mollie\Subscription\Presenter;

class OrderPresenter
{
    public function present(
        \Order $order,
        int $recurringOrderProductAttributeId,
        float $recurringOrderTotalTaxIncl
    ): RecurringOrderLazyArray {
        $orderProducts = $order->getCartProducts();

        foreach ($orderProducts as $orderProduct) {
            if ((int) $orderProduct['id_product_attribute'] !== $recurringOrderProductAttributeId) {
                $order->total_paid_tax_excl -= (float) $orderProduct['total_price_tax_excl'];

                continue;
            }

            $order->total_products = (float) $orderProduct['total_price_tax_excl'];
            $order->total_products_wt = (float) $orderProduct['total_price_tax_incl'];
            $order->total_paid_tax_incl = $recurringOrderTotalTaxIncl;
            $order->total_paid = $recurringOrderTotalTaxIncl;

            break;
        }

        $orderLazyArray = new RecurringOrderLazyArray($order);

        $orderLazyArray->setRecurringOrderProductAttributeId($recurringOrderProductAttributeId);

        return $orderLazyArray;
    }
}
