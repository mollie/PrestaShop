<?php

namespace Mollie\DTO;

class PaymentFeeData
{
    /** @var float */
    private $paymentFeeTaxIncl;
    /** @var float */
    private $paymentFeeTaxExcl;

    public function __construct(
        float $paymentFeeTaxIncl,
        float $paymentFeeTaxExcl
    ) {
        $this->paymentFeeTaxIncl = $paymentFeeTaxIncl;
        $this->paymentFeeTaxExcl = $paymentFeeTaxExcl;
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
}
