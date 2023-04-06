<?php

namespace Mollie\Subscription\Traits;

use Address;
use Mollie\Adapter\ToolsAdapter;
use Mollie\Decorator\RecurringOrderLazyArray;
use Mollie\Subscription\Handler\CustomerAddressUpdateHandler;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;
use MollieRecurringOrderDetailModuleFrontController;
use MolRecurringOrder;
use Order;
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

        $subscriptionShippingAddressUpdateHandler->handle((int) $address->id_customer, (int) $this->newAddressId, (int) $address->id);

        $this->newAddressId = null;
    }

    public function hookActionObjectAddressDeleteAfter(array $params): void
    {
        /** @var Address $deletedAddress */
        $deletedAddress = $params['object'];

        /** @var RecurringOrderRepositoryInterface $recurringOrderRepository */
        $recurringOrderRepository = $this->getService(RecurringOrderRepositoryInterface::class);

//        TODO reuse customerAddressUpdateHandler, this case is different as we don't need to save deleted address if it's not linked with subscription address.

        /** @var \MolRecurringOrder[]|null $orders */
        $orders = $recurringOrderRepository
            ->findAll()
            ->where('id_customer', '=', (int) $deletedAddress->id_customer)
            ->sqlWhere('id_address_delivery = ' . (int) $deletedAddress->id . ' OR id_address_invoice = ' . (int) $deletedAddress->id)
            ->getAll();

        if (!$orders) {
            return;
        }

        $newAddress = $deletedAddress;

        $newAddress->id = 0;
        $newAddress->deleted = 1;

        $newAddress->save();

        foreach ($orders as $order) {
            if ((int) $order->id_address_delivery === $deletedAddress->id) {
                $order->id_address_delivery = $newAddress->id;
            }

            if ((int) $order->id_address_invoice === $deletedAddress->id) {
                $order->id_address_invoice = $newAddress->id;
            }

            $order->date_update = $this->clock->getCurrentDate();

            $order->update();
        }
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
