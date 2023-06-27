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

use Address;
use Mollie\Adapter\Context;
use Mollie\Config\Config;
use Mollie\DTO\PaymentFeeData;
use Mollie\Exception\Code\ExceptionCode;
use Mollie\Exception\FailedToProvidePaymentFeeException;
use Mollie\Repository\AddressRepositoryInterface;
use Mollie\Utility\NumberUtility;
use MolPaymentMethod;
use PrestaShop\Decimal\DecimalNumber;
use PrestaShop\Decimal\Number;
use PrestaShop\Decimal\Operation\Rounding;
use TaxCalculator;

class PaymentFeeProvider implements PaymentFeeProviderInterface
{
    private const MAX_PERCENTAGE = 100.00;
    private const TEMPORARY_PRECISION = 6;

    /** @var Context */
    private $context;
    /** @var AddressRepositoryInterface */
    private $addressRepository;
    /** @var TaxCalculatorProvider */
    private $taxProvider;

    public function __construct(
        Context $context,
        AddressRepositoryInterface $addressRepository,
        TaxCalculatorProvider $taxProvider
    ) {
        $this->context = $context;
        $this->addressRepository = $addressRepository;
        $this->taxProvider = $taxProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentFee(MolPaymentMethod $paymentMethod, float $totalCartPrice): PaymentFeeData
    {
        // TODO handle exception on all calls.
        $totalDecimalCartPrice = NumberUtility::getNumber($totalCartPrice);
        $maxPercentage = NumberUtility::getNumber(self::MAX_PERCENTAGE);
        $surchargePercentage = NumberUtility::getNumber((float) $paymentMethod->surcharge_percentage);
        $surchargeFixedPriceTaxExcl = NumberUtility::getNumber($paymentMethod->surcharge_fixed_amount_tax_excl);

        /** @var Address|null $address */
        $address = $this->addressRepository->findOneBy([
            'id_address' => $this->context->getCustomerAddressInvoiceId(),
            'deleted' => 0,
        ]);

        if (!$address || !$address->id) {
            throw new FailedToProvidePaymentFeeException('Failed to find customer address', ExceptionCode::FAILED_TO_FIND_CUSTOMER_ADDRESS);
        }

        $taxCalculator = $this->taxProvider->getTaxCalculator(
            $paymentMethod->tax_rules_group_id,
            $address->id_country,
            $address->id_state
        );

        switch ($paymentMethod->surcharge) {
            case Config::FEE_FIXED_FEE:
                $totalFeePriceTaxExcl = $surchargeFixedPriceTaxExcl;
                $totalFeePriceTaxIncl = NumberUtility::getNumber($taxCalculator->addTaxes(
                    (float) $totalFeePriceTaxExcl->toPrecision(self::TEMPORARY_PRECISION, Rounding::ROUND_HALF_UP)
                ));

                return $this->returnFormattedResult($totalFeePriceTaxIncl, $totalFeePriceTaxExcl, $taxCalculator->getTotalRate());
            case Config::FEE_PERCENTAGE:
                $totalFeePriceTaxExcl = $totalDecimalCartPrice->times(
                    $surchargePercentage->dividedBy(
                        $maxPercentage
                    )
                );

                $totalFeePriceTaxIncl = NumberUtility::getNumber($taxCalculator->addTaxes(
                    (float) $totalFeePriceTaxExcl->toPrecision(self::TEMPORARY_PRECISION, Rounding::ROUND_HALF_UP)
                ));

                return $this->handleSurchargeMaxValue(
                    $paymentMethod->surcharge_limit,
                    $totalFeePriceTaxIncl,
                    $totalFeePriceTaxExcl,
                    $taxCalculator
                );
            case Config::FEE_FIXED_FEE_AND_PERCENTAGE:
                $totalFeePriceTaxExcl = $totalDecimalCartPrice->times(
                    $surchargePercentage->dividedBy(
                        $maxPercentage
                    )
                )->plus($surchargeFixedPriceTaxExcl);

                $totalFeePriceTaxIncl = NumberUtility::getNumber($taxCalculator->addTaxes(
                    (float) $totalFeePriceTaxExcl->toPrecision(self::TEMPORARY_PRECISION, Rounding::ROUND_HALF_UP)
                ));

                return $this->handleSurchargeMaxValue(
                    $paymentMethod->surcharge_limit,
                    $totalFeePriceTaxIncl,
                    $totalFeePriceTaxExcl,
                    $taxCalculator
                );
        }

        return new PaymentFeeData(0.00, 0.00, 0.00, false);
    }

    /**
     * @param string $surchargeLimit
     * @param Number|DecimalNumber $totalFeePriceTaxIncl
     * @param Number|DecimalNumber $totalFeePriceTaxExcl
     * @param TaxCalculator $taxCalculator
     *
     * @return PaymentFeeData
     */
    private function handleSurchargeMaxValue(
        string $surchargeLimit,
        $totalFeePriceTaxIncl,
        $totalFeePriceTaxExcl,
        TaxCalculator $taxCalculator
    ): PaymentFeeData {
        $surchargeMaxValue = NumberUtility::getNumber((float) $surchargeLimit);

        if ($surchargeMaxValue->isGreaterOrEqualThan(NumberUtility::getNumber(0)) && $totalFeePriceTaxIncl->isGreaterOrEqualThan($surchargeMaxValue)) {
            $totalFeePriceTaxIncl = $surchargeMaxValue;
            $totalFeePriceTaxExcl = NumberUtility::getNumber($taxCalculator->removeTaxes(
                (float) $surchargeMaxValue->toPrecision(self::TEMPORARY_PRECISION, Rounding::ROUND_HALF_UP)
            ));
        }

        return $this->returnFormattedResult($totalFeePriceTaxIncl, $totalFeePriceTaxExcl, $taxCalculator->getTotalRate());
    }

    /**
     * @param Number|DecimalNumber $totalFeePriceTaxIncl
     * @param Number|DecimalNumber $totalFeePriceTaxExcl
     * @param float $taxRate
     *
     * @return PaymentFeeData
     */
    private function returnFormattedResult(
        $totalFeePriceTaxIncl,
        $totalFeePriceTaxExcl,
        float $taxRate
    ): PaymentFeeData {
        $totalFeePriceTaxInclResult = (float) $totalFeePriceTaxIncl->toPrecision($this->context->getComputingPrecision(), Rounding::ROUND_HALF_UP);
        $totalFeePriceTaxExclResult = (float) $totalFeePriceTaxExcl->toPrecision($this->context->getComputingPrecision(), Rounding::ROUND_HALF_UP);

        return new PaymentFeeData(
            $totalFeePriceTaxInclResult,
            $totalFeePriceTaxExclResult,
            $taxRate,
            $totalFeePriceTaxInclResult > 0 && $totalFeePriceTaxExclResult > 0
        );
    }
}
