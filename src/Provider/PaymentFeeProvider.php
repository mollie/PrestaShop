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
use Mollie\Exception\FailedToProvidePaymentFeeException;
use Mollie\Repository\TaxRepositoryInterface;
use Mollie\Repository\TaxRuleRepositoryInterface;
use Mollie\Utility\TaxUtility;
use MolPaymentMethod;
use PrestaShop\Decimal\DecimalNumber;
use PrestaShop\Decimal\Operation\Rounding;
use Tax;
use TaxRule;

class PaymentFeeProvider implements PaymentFeeProviderInterface
{
    private const MAX_PERCENTAGE = '100';
    private const LOWEST_VALUE = '0';
    private const TEMPORARY_PRECISION = 6;

    /** @var Context */
    private $context;
    /** @var TaxUtility */
    private $taxUtility;
    /** @var TaxRuleRepositoryInterface */
    private $taxRuleRepository;
    /** @var TaxRepositoryInterface */
    private $taxRepository;

    public function __construct(
        Context $context,
        TaxUtility $taxUtility,
        TaxRuleRepositoryInterface $taxRuleRepository,
        TaxRepositoryInterface $taxRepository
    ) {
        $this->context = $context;
        $this->taxUtility = $taxUtility;
        $this->taxRuleRepository = $taxRuleRepository;
        $this->taxRepository = $taxRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentFee(MolPaymentMethod $paymentMethod, float $totalCartPrice): PaymentFeeData
    {
        // TODO handle exception on all calls.
        // TODO test it on 1.7.6, DecimalNumber could be issue
        $totalDecimalCartPrice = new DecimalNumber((string) $totalCartPrice);
        $maxPercentage = new DecimalNumber(self::MAX_PERCENTAGE);
        $surchargePercentage = new DecimalNumber($paymentMethod->surcharge_percentage);
        $surchargeFixedPriceTaxIncl = new DecimalNumber((string) $paymentMethod->surcharge_fixed_amount_tax_incl);
        $surchargeFixedPriceTaxExcl = new DecimalNumber((string) $paymentMethod->surcharge_fixed_amount_tax_excl);

        /** @var TaxRule|null $taxRule */
        $taxRule = $this->taxRuleRepository->findOneBy([
            'id_tax_rules_group' => $paymentMethod->tax_rules_group_id,
            'id_country' => $this->context->getCustomerAddressInvoiceId(),
        ]);

        if (!$taxRule || !$taxRule->id) {
            throw new FailedToProvidePaymentFeeException('Failed to find tax rules', FailedToProvidePaymentFeeException::FAILED_TO_FIND_TAX_RULES);
        }

        /** @var Tax|null $tax */
        $tax = $this->taxRepository->findOneBy([
            'id_tax' => $taxRule->id_tax,
        ]);

        if (!$tax || !$tax->id) {
            throw new FailedToProvidePaymentFeeException('Failed to find tax', FailedToProvidePaymentFeeException::FAILED_TO_FIND_TAX);
        }

        switch ($paymentMethod->surcharge) {
            case Config::FEE_FIXED_FEE:
                $totalFeePriceTaxIncl = $surchargeFixedPriceTaxIncl;
                $totalFeePriceTaxExcl = $surchargeFixedPriceTaxExcl;

                return new PaymentFeeData(
                    (float) $totalFeePriceTaxIncl->toPrecision($this->context->getComputingPrecision(), Rounding::ROUND_HALF_UP),
                    (float) $totalFeePriceTaxExcl->toPrecision($this->context->getComputingPrecision(), Rounding::ROUND_HALF_UP),
                    true
                );
            case Config::FEE_PERCENTAGE:
                $totalFeePriceTaxExcl = $totalDecimalCartPrice->times(
                    $surchargePercentage->dividedBy(
                        $maxPercentage
                    )
                );

                $totalFeePriceTaxIncl = $this->taxUtility->addTax(
                    (float) $totalFeePriceTaxExcl->toPrecision(self::TEMPORARY_PRECISION, Rounding::ROUND_HALF_UP),
                    $tax
                );

                $totalFeePriceTaxIncl = new DecimalNumber((string) $totalFeePriceTaxIncl);

                break;
            case Config::FEE_FIXED_FEE_AND_PERCENTAGE:
                $totalFeePriceTaxExcl = $totalDecimalCartPrice->times(
                    $surchargePercentage->dividedBy(
                        $maxPercentage
                    )
                )->plus($surchargeFixedPriceTaxExcl);

                $totalFeePriceTaxIncl = $this->taxUtility->addTax(
                    (float) $totalFeePriceTaxExcl->toPrecision(self::TEMPORARY_PRECISION, Rounding::ROUND_HALF_UP),
                    $tax
                );

                $totalFeePriceTaxIncl = new DecimalNumber((string) $totalFeePriceTaxIncl);

                break;
            case Config::FEE_NO_FEE:
            default:
                return new PaymentFeeData(0.00, 0.00, true);
        }

        $surchargeMaxValue = new DecimalNumber((string) $paymentMethod->surcharge_limit);
        $lowestValue = new DecimalNumber((string) self::LOWEST_VALUE);

        if ($surchargeMaxValue->isGreaterThan($lowestValue) && $totalFeePriceTaxIncl->isGreaterOrEqualThan($surchargeMaxValue)) {
            $totalFeePriceTaxIncl = $surchargeMaxValue;
            $totalFeePriceTaxExcl = $this->taxUtility->removeTax(
                (float) $surchargeMaxValue->toPrecision(self::TEMPORARY_PRECISION, Rounding::ROUND_HALF_UP),
                $tax
            );

            $totalFeePriceTaxExcl = new DecimalNumber((string) $totalFeePriceTaxExcl);
        }

        return new PaymentFeeData(
            (float) $totalFeePriceTaxIncl->toPrecision($this->context->getComputingPrecision(), Rounding::ROUND_HALF_UP),
            (float) $totalFeePriceTaxExcl->toPrecision($this->context->getComputingPrecision(), Rounding::ROUND_HALF_UP),
            true
        );
    }
}
