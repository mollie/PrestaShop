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
    public function hookActionObjectAddressAddAfter(array $params): void
    {
        /** @var Address $address */
        $address = $params['object'];

        /** @var ToolsAdapter $tools */
        $tools = $this->getService(ToolsAdapter::class);

        $customerId = (int) $address->id_customer;
        $oldAddressId = (int) $tools->getValue('id_address');
        $newAddressId = (int) $address->id;

        if (!$oldAddressId) {
            return;
        }

        /** @var MolRecurringOrder[] $orders */
        $orders = $this->getRecurringOrdersByCustomerAddress($customerId, $oldAddressId);

        if (!$orders) {
            //NOTE: No exception is needed as there could be no subscription orders with the old address
            return;
        }

        /** @var CustomerAddressUpdateHandler $subscriptionShippingAddressUpdateHandler */
        $subscriptionShippingAddressUpdateHandler = $this->getService(CustomerAddressUpdateHandler::class);

        $subscriptionShippingAddressUpdateHandler->handle($orders, $newAddressId, $oldAddressId);
    }

    public function hookActionObjectAddressUpdateAfter(array $params): void
    {
        /** @var Address $address */
        $address = $params['object'];

        $customerId = (int) $address->id_customer;
        $addressId = (int) $address->id;

        /** @var MolRecurringOrder[] $orders */
        $orders = $this->getRecurringOrdersByCustomerAddress($customerId, $addressId);

        if (!$orders) {
            //NOTE: No exception is needed as there could be no subscription orders with the old address
            return;
        }

        /**
         * NOTE: using handler just to update data_updated field
         */
        /** @var CustomerAddressUpdateHandler $subscriptionShippingAddressUpdateHandler */
        $subscriptionShippingAddressUpdateHandler = $this->getService(CustomerAddressUpdateHandler::class);

        $subscriptionShippingAddressUpdateHandler->handle($orders, $addressId, $addressId);
    }

    public function hookActionObjectAddressDeleteAfter(array $params): void
    {
        /** @var Address $deletedAddress */
        $deletedAddress = $params['object'];

        $customerId = (int) $deletedAddress->id_customer;
        $oldAddressId = (int) $deletedAddress->id;

        /** @var MolRecurringOrder[] $orders */
        $orders = $this->getRecurringOrdersByCustomerAddress($customerId, $oldAddressId);

        if (!$orders) {
            //NOTE: No exception is needed as there could be no subscription orders with the old address
            return;
        }

        $newAddress = $deletedAddress;

        $newAddress->id = 0;
        $newAddress->deleted = true;

        /*
         * NOTE: this triggers addAfter hook, which replaces old ID with the new one
         */
        $newAddress->save();
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

    private function getRecurringOrdersByCustomerAddress(int $customerId, int $oldAddressId): array
    {
        /** @var RecurringOrderRepositoryInterface $recurringOrderRepository */
        $recurringOrderRepository = $this->getService(RecurringOrderRepositoryInterface::class);

        return $recurringOrderRepository
            ->findAll()
            ->where('id_customer', '=', $customerId)
            ->sqlWhere('id_address_delivery = ' . $oldAddressId . ' OR id_address_invoice = ' . $oldAddressId)
            ->getResults();
    }
}
