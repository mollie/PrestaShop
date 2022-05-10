<?php

namespace Mollie\Application\Command;

use Mollie\DTO\ApplePay\Order;

final class CreateApplePayOrder
{
    /**
     * @var int
     */
    private $cartId;
    /**
     * @var Order
     */
    private $order;
    /**
     * @var string
     */
    private $cardToken;

    public function __construct(int $cartId, Order $order, string $cardToken)
    {
        $this->cartId = $cartId;
        $this->order = $order;
        $this->cardToken = $cardToken;
    }

    public function getCartId(): int
    {
        return $this->cartId;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getCardToken(): string
    {
        return $this->cardToken;
    }
}
