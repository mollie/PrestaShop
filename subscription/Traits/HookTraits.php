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
use PrestaShopCollection;

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

        $customerId = (int) $address->id_customer;
        $newAddressId = (int) $this->newAddressId;
        $oldAddressId = (int) $address->id;

        /** @var MolRecurringOrder[]|null $orders */
        $orders = $this->getRecurringOrdersByCustomerAddress($customerId, $oldAddressId);

        if (!$orders) {
            //NOTE: No exception is needed as there could be no subscription orders with the old address
            $this->newAddressId = null;

            return;
        }

        /** @var CustomerAddressUpdateHandler $subscriptionShippingAddressUpdateHandler */
        $subscriptionShippingAddressUpdateHandler = $this->getService(CustomerAddressUpdateHandler::class);

        $subscriptionShippingAddressUpdateHandler->handle($orders, $newAddressId, $oldAddressId);

        $this->newAddressId = null;
    }

    public function hookActionObjectAddressDeleteAfter(array $params): void
    {
        /** @var Address $deletedAddress */
        $deletedAddress = $params['object'];

        $customerId = (int) $deletedAddress->id_customer;
        $oldAddressId = (int) $deletedAddress->id;

        /** @var MolRecurringOrder[]|null $orders */
        $orders = $this->getRecurringOrdersByCustomerAddress($customerId, $oldAddressId);

        if (!$orders) {
            //NOTE: No exception is needed as there could be no subscription orders with the old address

            return;
        }

        $newAddress = $deletedAddress;

        $newAddress->id = 0;
        $newAddress->deleted = 1;
        $newAddress->save();

        $newAddressId = (int) $newAddress->id;

        /** @var CustomerAddressUpdateHandler $subscriptionShippingAddressUpdateHandler */
        $subscriptionShippingAddressUpdateHandler = $this->getService(CustomerAddressUpdateHandler::class);

        $subscriptionShippingAddressUpdateHandler->handle($orders, $newAddressId, $oldAddressId);
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

    private function getRecurringOrdersByCustomerAddress(int $customerId, int $oldAddressId): ?PrestaShopCollection
    {
        /** @var RecurringOrderRepositoryInterface $recurringOrderRepository */
        $recurringOrderRepository = $this->getService(RecurringOrderRepositoryInterface::class);

        return $recurringOrderRepository
            ->findAll()
            ->where('id_customer', '=', $customerId)
            ->sqlWhere('id_address_delivery = ' . $oldAddressId . ' OR id_address_invoice = ' . $oldAddressId)
            ->getAll();
    }
}
