<?php

namespace Mollie\DTO;

class CreateOrderPaymentFeeActionData
{
    /** @var int */
    private $orderId;
    /** @var int */
    private $cartId;
    /** @var float */
    private $paymentFeeTaxIncl;
    /** @var float */
    private $paymentFeeTaxExcl;

    public function __construct(
        int $orderId,
        int $cartId,
        float $paymentFeeTaxIncl,
        float $paymentFeeTaxExcl
    ) {
        $this->orderId = $orderId;
        $this->cartId = $cartId;
        $this->paymentFeeTaxIncl = $paymentFeeTaxIncl;
        $this->paymentFeeTaxExcl = $paymentFeeTaxExcl;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * @return int
     */
    public function getCartId(): int
    {
        return $this->cartId;
    }

    /**
     * @return float
     */
    public function getPaymentFeeTaxIncl(): float
    {
        return $this->paymentFeeTaxIncl;
    }

    /**
     * @return float
     */
    public function getPaymentFeeTaxExcl(): float
    {
        return $this->paymentFeeTaxExcl;
    }

    public static function create(
        int $orderId,
        int $cartId,
        float $paymentFeeTaxIncl,
        float $paymentFeeTaxExcl
    ): CreateOrderPaymentFeeActionData {
        return new self(
            $orderId,
            $cartId,
            $paymentFeeTaxIncl,
            $paymentFeeTaxExcl
        );
    }
}
