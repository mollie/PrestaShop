<?php

namespace Mollie\Subscription\Handler;

use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;
use Mollie\Subscription\Utility\ClockInterface;
use MolRecurringOrder;

/**
 * NOTE: this handler is used specifically for address update,
 * where address was already used for subscription product.
 *
 * actionObjectAddressAddAfter and actionObjectAddressUpdateAfter hooks are called one after another
 * to get previous address and current address IDs.
 */
class CustomerAddressUpdateHandler
{
    /** @var RecurringOrderRepositoryInterface */
    private $recurringOrderRepository;
    /** @var ClockInterface */
    private $clock;

    public function __construct(
        RecurringOrderRepositoryInterface $recurringOrderRepository,
        ClockInterface $clock
    ) {
        $this->recurringOrderRepository = $recurringOrderRepository;
        $this->clock = $clock;
    }

    public function handle(int $customerId, int $newAddressId, int $oldAddressId): void
    {
        /** @var \MolRecurringOrder[]|null $orders */
        $orders = $this->recurringOrderRepository
            ->findAll()
            ->where('id_customer', '=', $customerId)
            ->sqlWhere('id_address_delivery = ' . $oldAddressId . ' OR id_address_invoice = ' . $oldAddressId)
            ->getAll();

        if (!$orders) {
            //NOTE: No exception is needed as there could be no subscription orders with the old address
            return;
        }

        foreach ($orders as $order) {
            if ((int) $order->id_address_delivery === $oldAddressId) {
                $order->id_address_delivery = $newAddressId;
            }

            if ((int) $order->id_address_invoice === $oldAddressId) {
                $order->id_address_invoice = $newAddressId;
            }

            $order->date_update = $this->clock->getCurrentDate();

            $order->update();
        }
    }
}
