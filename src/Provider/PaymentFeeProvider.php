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
use Mollie;
use Mollie\Adapter\Context;
use Mollie\Calculator\PaymentFeeCalculator;
use Mollie\Config\Config;
use Mollie\DTO\PaymentFeeData;
use Mollie\Factory\ModuleFactory;
use Mollie\Repository\AddressRepositoryInterface;
use Mollie\Utility\ExceptionUtility;
use Mollie\Validator\PaymentFeeValidator;
use Mollie\Logger\LoggerInterface;
use MolPaymentMethod;
use Throwable;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentFeeProvider implements PaymentFeeProviderInterface
{
    private const FILE_NAME = 'PaymentFeeProvider';

    /** @var Context */
    private $context;
    /** @var AddressRepositoryInterface */
    private $addressRepository;
    /** @var TaxCalculatorProvider */
    private $taxProvider;
    /** @var Mollie */
    private $module;
    /** @var PaymentFeeValidator */
    private $paymentFeeValidator;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        Context $context,
        AddressRepositoryInterface $addressRepository,
        TaxCalculatorProvider $taxProvider,
        ModuleFactory $module,
        PaymentFeeValidator $paymentFeeValidator,
        LoggerInterface $logger
    ) {
        $this->context = $context;
        $this->addressRepository = $addressRepository;
        $this->taxProvider = $taxProvider;
        $this->module = $module->getModule();
        $this->paymentFeeValidator = $paymentFeeValidator;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentFee(MolPaymentMethod $paymentMethod, float $totalCartPriceTaxIncl): PaymentFeeData
    {
        try {
            $this->paymentFeeValidator->validatePaymentFeePercentage($paymentMethod);

            $surchargeFixedPriceTaxExcl = (float) $paymentMethod->surcharge_fixed_amount_tax_excl;
            $surchargePercentage = (float) $paymentMethod->surcharge_percentage;
            $surchargeLimit = (float) $paymentMethod->surcharge_limit;

            /** @var Address|null $address */
            $address = $this->addressRepository->findOneBy([
                'id_address' => $this->context->getCustomerAddressInvoiceId(),
                'deleted' => 0,
            ]);

            if (!$address || !$address->id) {
                return new PaymentFeeData(0.00, 0.00, 0.00, false);
            }

            $taxCalculator = $this->taxProvider->getTaxCalculator(
                (int) $paymentMethod->tax_rules_group_id,
                (int) $address->id_country,
                (int) $address->id_state
            );

            $paymentFeeCalculator = new PaymentFeeCalculator($taxCalculator, $this->context);

            switch ($paymentMethod->surcharge) {
                case Config::FEE_FIXED_FEE:
                    $paymentFeeData = $paymentFeeCalculator->calculateFixedFee(
                        $surchargeFixedPriceTaxExcl
                    );
                    break;
                case Config::FEE_PERCENTAGE:
                    $paymentFeeData = $paymentFeeCalculator->calculatePercentageFee(
                        $totalCartPriceTaxIncl,
                        $surchargePercentage,
                        $surchargeLimit
                    );
                    break;
                case Config::FEE_FIXED_FEE_AND_PERCENTAGE:
                    $paymentFeeData = $paymentFeeCalculator->calculatePercentageAndFixedPriceFee(
                        $totalCartPriceTaxIncl,
                        $surchargePercentage,
                        $surchargeFixedPriceTaxExcl,
                        $surchargeLimit
                    );
                    break;
                default:
                    $paymentFeeData = new PaymentFeeData(0.00, 0.00, 0.00, false);
            }

            $this->paymentFeeValidator->validatePaymentFeeAmount($paymentFeeData, $totalCartPriceTaxIncl);

            return $paymentFeeData;
        } catch (Throwable $e) {
            $this->logger->error(sprintf('%s - Error while calculating payment fee', self::FILE_NAME), [
                'exceptions' => ExceptionUtility::getExceptions($e),
            ]);

            // TODO: should we throw an exception?
            return new PaymentFeeData(0.00, 0.00, 0.00, false);
        }
    }

    public function getPaymentFeeText(float $paymentFeeTaxIncl): string
    {
        if (0 == $paymentFeeTaxIncl) {
            return '';
        }

        return $paymentFeeTaxIncl < 0 ? $this->module->l('Discount: %1s', self::FILE_NAME) : $this->module->l('Payment Fee: %1s', self::FILE_NAME);
    }
}
