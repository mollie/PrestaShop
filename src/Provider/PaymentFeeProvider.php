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
use Mollie\Calculator\PaymentFeeCalculator;
use Mollie\Config\Config;
use Mollie\DTO\PaymentFeeData;
use Mollie\Exception\Code\ExceptionCode;
use Mollie\Exception\FailedToProvidePaymentFeeException;
use Mollie\Repository\AddressRepositoryInterface;
use MolPaymentMethod;

class PaymentFeeProvider implements PaymentFeeProviderInterface
{
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
    public function getPaymentFee(MolPaymentMethod $paymentMethod, float $totalCartPriceTaxIncl): PaymentFeeData
    {
        // TODO handle exception on all calls.
        $surchargeFixedPriceTaxExcl = $paymentMethod->surcharge_fixed_amount_tax_excl;
        $surchargePercentage = (float) $paymentMethod->surcharge_percentage;
        $surchargeLimit = (float) $paymentMethod->surcharge_limit;

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

        $paymentFeeCalculator = new PaymentFeeCalculator($taxCalculator, $this->context);

        // TODO it would be good to use Abstract class, which would hold common private methods and then create separate services, which would provide calculated fee.
        switch ($paymentMethod->surcharge) {
            case Config::FEE_FIXED_FEE:
                return $paymentFeeCalculator->calculateFixedFee(
                    $surchargeFixedPriceTaxExcl
                );
            case Config::FEE_PERCENTAGE:
                return $paymentFeeCalculator->calculatePercentageFee(
                    $totalCartPriceTaxIncl,
                    $surchargePercentage,
                    $surchargeLimit
                );
            case Config::FEE_FIXED_FEE_AND_PERCENTAGE:
                return $paymentFeeCalculator->calculatePercentageAndFixedPriceFee(
                    $totalCartPriceTaxIncl,
                    $surchargePercentage,
                    $surchargeFixedPriceTaxExcl,
                    $surchargeLimit
                );
        }

        return new PaymentFeeData(0.00, 0.00, 0.00, false);
    }
}
