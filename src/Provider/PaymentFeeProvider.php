<?php
/**
 * Copyright (c) 2012-2020, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 *
 * @category   Mollie
 *
 * @see       https://www.mollie.nl
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Provider;

use Mollie\Adapter\Context;
use Mollie\Config\Config;
use Mollie\DTO\PaymentFeeData;
use MolPaymentMethod;
use PrestaShop\Decimal\Exception\DivisionByZeroException;
use PrestaShop\Decimal\Number;
use PrestaShop\Decimal\Operation\Rounding;

class PaymentFeeProvider implements PaymentFeeProviderInterface
{
    private const MAX_PERCENTAGE = '100';
    private const LOWEST_VALUE = '0';
    private const CALCULATION_PRECISION = 6;

    /**
     * @var OrderTotalProviderInterface
     */
    private $orderTotalProvider;
    /** @var Context */
    private $context;

    public function __construct(
        OrderTotalProviderInterface $orderTotalProvider,
        Context $context
    ) {
        $this->orderTotalProvider = $orderTotalProvider;
        $this->context = $context;
    }

//    public function getPaymentFee(MolPaymentMethod $paymentMethod): PaymentFeeData
//    {
//        return PaymentFeeUtility::getPaymentFee($paymentMethod, $this->orderTotalProvider->getOrderTotal());
//    }

    /**
     * @param MolPaymentMethod $paymentMethod
     *
     * @return PaymentFeeData
     *
     * @throws DivisionByZeroException
     */
    public function getPaymentFee(MolPaymentMethod $paymentMethod): PaymentFeeData
    {
        // TODO exception handle in multiple services

        $totalDecimalCartPrice = new Number((string) $this->orderTotalProvider->getOrderTotal());
        $maxPercentage = new Number(self::MAX_PERCENTAGE);
        $surchargePercentage = new Number($paymentMethod->surcharge_percentage);
        $surchargeFixedPriceTaxIncl = new Number($paymentMethod->surcharge_fixed_amount_tax_incl);
        $surchargeFixedPriceTaxExcl = new Number($paymentMethod->surcharge_fixed_amount_tax_excl);

        switch ($paymentMethod->surcharge) {
            case Config::FEE_FIXED_FEE:
                $totalFeePriceTaxIncl = $surchargeFixedPriceTaxIncl;
                $totalFeePriceTaxExcl = $surchargeFixedPriceTaxExcl;

                break;
            case Config::FEE_PERCENTAGE:
                $totalFeePriceTaxExcl = $totalDecimalCartPrice->times(
                    $surchargePercentage->dividedBy(
                        $maxPercentage
                    )
                );

                $totalFeePriceTaxIncl = $this->addTax($totalFeePriceTaxExcl);

                break;
            case Config::FEE_FIXED_FEE_AND_PERCENTAGE:
                $totalFeePriceTaxExcl = $totalDecimalCartPrice->times(
                    $surchargePercentage->dividedBy(
                        $maxPercentage
                    )
                )->plus($surchargeFixedPriceTaxExcl);

                $totalFeePriceTaxIncl = $this->addTax($totalFeePriceTaxExcl);

                break;
            case Config::FEE_NO_FEE:
            default:
                return new PaymentFeeData(0, 0);
        }

        $surchargeMaxValue = new Number($paymentMethod->surcharge_limit);
        $lowestValue = new Number(self::LOWEST_VALUE);

        if ($surchargeMaxValue->isGreaterThan($lowestValue) && $totalFeePriceTaxIncl->isGreaterOrEqualThan($surchargeMaxValue)) {
            $totalFeePriceTaxIncl = $surchargeMaxValue;
            $totalFeePriceTaxExcl = $this->removeTax($surchargeMaxValue);
        }

        return new PaymentFeeData(
            $totalFeePriceTaxIncl->toPrecision($this->context->getComputingPrecision(), Rounding::ROUND_HALF_UP),
            $totalFeePriceTaxExcl->toPrecision($this->context->getComputingPrecision(), Rounding::ROUND_HALF_UP)
        );
    }

    private function addTax(Number $totalFeePrice): Number
    {
//        TODO separate money service or smth
//        TODO get selected tax
        $tax = new \Tax();
        $tax->rate = 21;
        $tax_calculator = new \TaxCalculator(array($tax));
        return new Number((string) $tax_calculator->addTaxes($totalFeePrice->toPrecision(self::CALCULATION_PRECISION)));
    }

    private function removeTax(Number $totalFeePrice): Number
    {
        //        TODO get selected tax
        $tax = new \Tax();
        $tax->rate = 21;
        $tax_calculator = new \TaxCalculator(array($tax));
        return new Number((string) $tax_calculator->removeTaxes($totalFeePrice->toPrecision(self::CALCULATION_PRECISION)));
    }
}
