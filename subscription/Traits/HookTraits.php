<?php

namespace Mollie\Subscription\Traits;

use Address;
use Mollie\Adapter\ToolsAdapter;
use Mollie\Decorator\RecurringOrderLazyArray;
use Mollie\Subscription\Handler\CustomerAddressUpdateHandler;
use MollieRecurringOrderDetailModuleFrontController;
use MolRecurringOrder;
use Order;
use PrestaShop\PrestaShop\Adapter\Presenter\Order\OrderDetailLazyArray;
use PrestaShop\PrestaShop\Adapter\Presenter\Order\OrderLazyArray;

/**
 * NOTE: used this hook trait as we need to access private property's data during single code execution.
 */
trait HookTraits
{
    private $newAddressId;

    public function hookActionObjectAddressAddAfter(array $params): void
    {
        /**
         * NOTE: If on update customer address action address gets re-created, then new address gets detached
         * from original order. Next time on address update it won't be re-created, instead it will be updated.
         * In this case this hook won't be active.
         */

        /** @var Address $address */
        $address = $params['object'];

        $this->newAddressId = $address->id;
    }

    public function hookActionObjectAddressUpdateAfter(array $params): void
    {
        if (!$this->newAddressId) {
            return;
        }

        /** @var Address $address */
        $address = $params['object'];

        /** @var CustomerAddressUpdateHandler $subscriptionShippingAddressUpdateHandler */
        $subscriptionShippingAddressUpdateHandler = $this->getService(CustomerAddressUpdateHandler::class);

        $subscriptionShippingAddressUpdateHandler->handle($address->id_customer, $this->newAddressId, $address->id);

        $this->newAddressId = null;
    }

    public function hookActionPresentOrder(array &$params): void
    {
        if (!$this->context->controller instanceof MollieRecurringOrderDetailModuleFrontController) {
            return;
        }

        /** @var OrderLazyArray $orderLazyArray */
        $orderLazyArray = $params['presentedOrder'];

        $orderDetails = $orderLazyArray->getDetails();

        $order = new Order($orderDetails->getId());

        /** @var ToolsAdapter $tools */
        $tools = $this->getService(ToolsAdapter::class);

        $molRecurringOrderId = (int) $tools->getValue('id_mol_recurring_order');

        if (!$molRecurringOrderId) {
            return;
        }

        $molRecurringOrder = new MolRecurringOrder($molRecurringOrderId);

        if (!$molRecurringOrder->id_mol_recurring_orders_product) {
            return;
        }

        $params['presentedOrder'] = new RecurringOrderLazyArray($order, $molRecurringOrder);
    }
}
