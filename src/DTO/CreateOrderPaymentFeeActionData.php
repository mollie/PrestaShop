<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

namespace Mollie\DTO;

if (!defined('_PS_VERSION_')) {
    exit;
}

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

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getCartId(): int
    {
        return $this->cartId;
    }

    public function getPaymentFeeTaxIncl(): float
    {
        return $this->paymentFeeTaxIncl;
    }

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
