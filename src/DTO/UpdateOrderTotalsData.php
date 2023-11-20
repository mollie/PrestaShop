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

class UpdateOrderTotalsData
{
    /** @var int */
    private $orderId;
    /** @var float */
    private $paymentFeeTaxIncl;
    /** @var float */
    private $paymentFeeTaxExcl;
    /** @var float */
    private $transactionAmount;
    /** @var float */
    private $originalCartAmountTaxIncl;
    /** @var float */
    private $originalCartAmountTaxExcl;

    public function __construct(
        int $orderId,
        float $paymentFeeTaxIncl,
        float $paymentFeeTaxExcl,
        float $transactionAmount,
        float $originalCartAmountTaxIncl,
        float $originalCartAmountTaxExcl
    ) {
        $this->orderId = $orderId;
        $this->paymentFeeTaxIncl = $paymentFeeTaxIncl;
        $this->paymentFeeTaxExcl = $paymentFeeTaxExcl;
        $this->transactionAmount = $transactionAmount;
        $this->originalCartAmountTaxIncl = $originalCartAmountTaxIncl;
        $this->originalCartAmountTaxExcl = $originalCartAmountTaxExcl;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
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

    /**
     * @return float
     */
    public function getTransactionAmount(): float
    {
        return $this->transactionAmount;
    }

    /**
     * @return float
     */
    public function getOriginalCartAmountTaxIncl(): float
    {
        return $this->originalCartAmountTaxIncl;
    }

    /**
     * @return float
     */
    public function getOriginalCartAmountTaxExcl(): float
    {
        return $this->originalCartAmountTaxExcl;
    }

    public static function create(
        int $orderId,
        float $paymentFeeTaxIncl,
        float $paymentFeeTaxExcl,
        float $transactionAmount,
        float $originalCartAmountTaxIncl,
        float $originalCartAmountTaxExcl
    ): UpdateOrderTotalsData {
        return new self(
            $orderId,
            $paymentFeeTaxIncl,
            $paymentFeeTaxExcl,
            $transactionAmount,
            $originalCartAmountTaxIncl,
            $originalCartAmountTaxExcl
        );
    }
}
