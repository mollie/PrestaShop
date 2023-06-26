<?php

namespace Mollie\DTO;

class PaymentFeeData
{
    /** @var float */
    private $paymentFeeTaxIncl;
    /** @var float */
    private $paymentFeeTaxExcl;
    /** @var float */
    private $taxRate;
    /** @var bool */
    private $active;

    public function __construct(
        float $paymentFeeTaxIncl,
        float $paymentFeeTaxExcl,
        float $taxRate,
        bool $active
    ) {
        $this->paymentFeeTaxIncl = $paymentFeeTaxIncl;
        $this->paymentFeeTaxExcl = $paymentFeeTaxExcl;
        $this->taxRate = $taxRate;
        $this->active = $active;
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
    public function getTaxRate(): float
    {
        return $this->taxRate;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }
}
