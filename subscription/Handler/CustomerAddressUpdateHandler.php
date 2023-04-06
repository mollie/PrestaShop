<?php

namespace Mollie\Subscription\Handler;

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
    /** @var ClockInterface */
    private $clock;

    public function __construct(ClockInterface $clock)
    {
        $this->clock = $clock;
    }

    /**
     * @param MolRecurringOrder[] $orders
     * @param int $newAddressId
     * @param int $oldAddressId
     *
     * @return void
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function handle(array $orders, int $newAddressId, int $oldAddressId): void
    {
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
