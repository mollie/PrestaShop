<?php

namespace Mollie\Application\Command;

final class UpdateApplePayShippingMethod
{
    /**
     * @var int
     */
    private $carrierId;
    /**
     * @var int
     */
    private $cartId;

    public function __construct(int $carrierId, int $cartId)
    {
        $this->carrierId = $carrierId;
        $this->cartId = $cartId;
    }

    public function getCarrierId(): int
    {
        return $this->carrierId;
    }

    public function getCartId(): int
    {
        return $this->cartId;
    }
}
