<?php

namespace Mollie\Decorator;

use Address;
use AddressFormat;
use MolRecurringOrder;
use Order;
use PrestaShop\PrestaShop\Adapter\Presenter\Object\ObjectPresenter;
use PrestaShop\PrestaShop\Adapter\Presenter\Order\OrderLazyArray;

class RecurringOrderLazyArray extends OrderLazyArray
{
    /** @var \MolRecurringOrder */
    private $molRecurringOrder;
    /** @var Order */
    private $order;
    /** @var ObjectPresenter */
    private $objectPresenter;

    public function __construct(Order $order, MolRecurringOrder $molRecurringOrder)
    {
        parent::__construct($order);

        $this->molRecurringOrder = $molRecurringOrder;
        $this->order = $order;
        $this->objectPresenter = new ObjectPresenter();
    }

    public function getIdAddressDelivery(): int
    {
        return $this->molRecurringOrder->id_address_delivery;
    }

    public function getIdAddressInvoice(): int
    {
        return $this->molRecurringOrder->id_address_invoice;
    }

    /**
     * @arrayAccess
     *
     * @return array
     */
    public function getAddresses(): array
    {
        $order = $this->order;

        $orderAddresses = [
            'delivery' => [],
            'invoice' => [],
        ];

        $addressDelivery = new Address($this->getIdAddressDelivery());
        $addressInvoice = new Address($this->getIdAddressInvoice());

        if (!$order->isVirtual()) {
            $orderAddresses['delivery'] = $this->objectPresenter->present($addressDelivery);
            $orderAddresses['delivery']['formatted'] =
                AddressFormat::generateAddress($addressDelivery, [], '<br />');
        }

        $orderAddresses['invoice'] = $this->objectPresenter->present($addressInvoice);
        $orderAddresses['invoice']['formatted'] = AddressFormat::generateAddress($addressInvoice, [], '<br />');

        return $orderAddresses;
    }
}
