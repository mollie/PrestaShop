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
use Mollie\Exception\FailedToProvideTaxException;
use Mollie\Repository\AddressRepositoryInterface;
use Mollie\Utility\TaxUtility;
use MolPaymentMethod;
use PrestaShop\Decimal\DecimalNumber;
use PrestaShop\Decimal\Operation\Rounding;
use Tax;

class PaymentFeeProvider implements PaymentFeeProviderInterface
{
    private const MAX_PERCENTAGE = '100';
    private const TEMPORARY_PRECISION = 6;

    /** @var Context */
    private $context;
    /** @var TaxUtility */
    private $taxUtility;
    /** @var AddressRepositoryInterface */
    private $addressRepository;
    /** @var TaxProvider */
    private $taxProvider;

    public function __construct(
        Context $context,
        TaxUtility $taxUtility,
        AddressRepositoryInterface $addressRepository,
        TaxProvider $taxProvider
    ) {
        $this->context = $context;
        $this->taxUtility = $taxUtility;
        $this->addressRepository = $addressRepository;
        $this->taxProvider = $taxProvider;
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
        $surchargeFixedPriceTaxExcl = new DecimalNumber((string) $paymentMethod->surcharge_fixed_amount_tax_excl);

        /** @var Address|null $address */
        $address = $this->addressRepository->findOneBy([
            'id_address' => $this->context->getCustomerAddressInvoiceId(),
            'deleted' => 0,
        ]);

        if (!$address || !$address->id) {
            throw new FailedToProvidePaymentFeeException('Failed to find customer address', ExceptionCode::FAILED_TO_FIND_CUSTOMER_ADDRESS);
        }

        // TODO allign on what to do on missing tax if country with states is used
        try {
            $tax = $this->taxProvider->getTax(
                $paymentMethod->tax_rules_group_id,
                $address->id_country,
                $address->id_state
            );
        } catch (FailedToProvideTaxException $exception) {
            throw new FailedToProvidePaymentFeeException('Failed to get tax', ExceptionCode::FAILED_TO_GET_TAX);
        }

        switch ($paymentMethod->surcharge) {
            case Config::FEE_FIXED_FEE:
                $totalFeePriceTaxExcl = $surchargeFixedPriceTaxExcl;
                $totalFeePriceTaxIncl = new DecimalNumber((string) $this->taxUtility->addTax(
                    (float) $totalFeePriceTaxExcl->toPrecision(self::TEMPORARY_PRECISION, Rounding::ROUND_HALF_UP),
                    $tax
                ));

                return $this->returnFormattedResult($totalFeePriceTaxIncl, $totalFeePriceTaxExcl);
            case Config::FEE_PERCENTAGE:
                $totalFeePriceTaxExcl = $totalDecimalCartPrice->times(
                    $surchargePercentage->dividedBy(
                        $maxPercentage
                    )
                );

                $totalFeePriceTaxIncl = new DecimalNumber((string) $this->taxUtility->addTax(
                    (float) $totalFeePriceTaxExcl->toPrecision(self::TEMPORARY_PRECISION, Rounding::ROUND_HALF_UP),
                    $tax
                ));

                return $this->handleSurchargeMaxValue(
                    $paymentMethod->surcharge_limit,
                    $totalFeePriceTaxIncl,
                    $totalFeePriceTaxExcl,
                    $tax
                );
            case Config::FEE_FIXED_FEE_AND_PERCENTAGE:
                $totalFeePriceTaxExcl = $totalDecimalCartPrice->times(
                    $surchargePercentage->dividedBy(
                        $maxPercentage
                    )
                )->plus($surchargeFixedPriceTaxExcl);

                $totalFeePriceTaxIncl = new DecimalNumber((string) $this->taxUtility->addTax(
                    (float) $totalFeePriceTaxExcl->toPrecision(self::TEMPORARY_PRECISION, Rounding::ROUND_HALF_UP),
                    $tax
                ));

                return $this->handleSurchargeMaxValue(
                    $paymentMethod->surcharge_limit,
                    $totalFeePriceTaxIncl,
                    $totalFeePriceTaxExcl,
                    $tax
                );
        }

        return new PaymentFeeData(0.00, 0.00, true);
    }

    private function handleSurchargeMaxValue(
        string $surchargeLimit,
        DecimalNumber $totalFeePriceTaxIncl,
        DecimalNumber $totalFeePriceTaxExcl,
        Tax $tax
    ): PaymentFeeData {
        $surchargeMaxValue = new DecimalNumber($surchargeLimit);

        if ($surchargeMaxValue->isGreaterOrEqualThanZero() && $totalFeePriceTaxIncl->isGreaterOrEqualThan($surchargeMaxValue)) {
            $totalFeePriceTaxIncl = $surchargeMaxValue;
            $totalFeePriceTaxExcl = new DecimalNumber((string) $this->taxUtility->removeTax(
                (float) $surchargeMaxValue->toPrecision(self::TEMPORARY_PRECISION, Rounding::ROUND_HALF_UP),
                $tax
            ));
        }

        return $this->returnFormattedResult($totalFeePriceTaxIncl, $totalFeePriceTaxExcl);
    }

    private function returnFormattedResult(DecimalNumber $totalFeePriceTaxIncl, DecimalNumber $totalFeePriceTaxExcl): PaymentFeeData
    {
        return new PaymentFeeData(
            (float) $totalFeePriceTaxIncl->toPrecision($this->context->getComputingPrecision(), Rounding::ROUND_HALF_UP),
            (float) $totalFeePriceTaxExcl->toPrecision($this->context->getComputingPrecision(), Rounding::ROUND_HALF_UP),
            true
        );
    }
}
