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

namespace Mollie\Handler\PaymentOption;

use Configuration;
use Customer;
use Mollie\Api\Types\PaymentMethod;
use Mollie\Config\Config;
use Mollie\Provider\PaymentOption\BancontactPaymentOptionProvider;
use Mollie\Provider\PaymentOption\BasePaymentOptionProvider;
use Mollie\Provider\PaymentOption\CreditCardPaymentOptionProvider;
use Mollie\Provider\PaymentOption\CreditCardSingleClickPaymentOptionProvider;
use Mollie\Provider\PaymentOption\IdealPaymentOptionProvider;
use Mollie\Repository\MolCustomerRepository;
use MolPaymentMethod;

class PaymentOptionHandler implements PaymentOptionHandlerInterface
{
    /**
     * @var BasePaymentOptionProvider
     */
    private $basePaymentOptionProvider;

    /**
     * @var CreditCardPaymentOptionProvider
     */
    private $creditCardPaymentOptionProvider;

    /**
     * @var IdealPaymentOptionProvider
     */
    private $idealPaymentOptionProvider;
    /**
     * @var MolCustomerRepository
     */
    private $customerRepository;
    /**
     * @var Customer
     */
    private $customer;
    /**
     * @var CreditCardSingleClickPaymentOptionProvider
     */
    private $cardSingleClickPaymentOptionProvider;
    /** @var BancontactPaymentOptionProvider */
    private $bancontactPaymentOptionProvider;

    public function __construct(
        BasePaymentOptionProvider $basePaymentOptionProvider,
        CreditCardPaymentOptionProvider $creditCardPaymentOptionProvider,
        CreditCardSingleClickPaymentOptionProvider $cardSingleClickPaymentOptionProvider,
        IdealPaymentOptionProvider $idealPaymentOptionProvider,
        MolCustomerRepository $customerRepository,
        Customer $customer,
        BancontactPaymentOptionProvider $bancontactPaymentOptionProvider
    ) {
        $this->basePaymentOptionProvider = $basePaymentOptionProvider;
        $this->creditCardPaymentOptionProvider = $creditCardPaymentOptionProvider;
        $this->idealPaymentOptionProvider = $idealPaymentOptionProvider;
        $this->customerRepository = $customerRepository;
        $this->customer = $customer;
        $this->cardSingleClickPaymentOptionProvider = $cardSingleClickPaymentOptionProvider;
        $this->bancontactPaymentOptionProvider = $bancontactPaymentOptionProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(MolPaymentMethod $paymentMethod)
    {
        if ($this->isIdealPaymentMethod($paymentMethod)) {
            return $this->idealPaymentOptionProvider->getPaymentOption($paymentMethod);
        }

        if ($this->isCreditCardPaymentMethod($paymentMethod)) {
            if ($this->isIFrame()) {
                return $this->creditCardPaymentOptionProvider->getPaymentOption($paymentMethod);
            } elseif ($this->isSingleClick()) {
                return $this->cardSingleClickPaymentOptionProvider->getPaymentOption($paymentMethod);
            }
        }
        if ($this->isBancontactWithQRCodePaymentMethod($paymentMethod)) {
            return $this->bancontactPaymentOptionProvider->getPaymentOption($paymentMethod);
        }

        return $this->basePaymentOptionProvider->getPaymentOption($paymentMethod);
    }

    /**
     * @param MolPaymentMethod $paymentMethod
     *
     * @return bool
     */
    private function isIdealPaymentMethod(MolPaymentMethod $paymentMethod)
    {
        if ($paymentMethod->getPaymentMethodName() !== PaymentMethod::IDEAL) {
            return false;
        }

        if (Configuration::get(Config::MOLLIE_ISSUERS) !== Config::ISSUERS_ON_CLICK) {
            return false;
        }

        return true;
    }

    /**
     * @param MolPaymentMethod $paymentMethod
     *
     * @return bool
     */
    private function isCreditCardPaymentMethod(MolPaymentMethod $paymentMethod): bool
    {
        return PaymentMethod::CREDITCARD === $paymentMethod->getPaymentMethodName();
    }

    /**
     * @param MolPaymentMethod $paymentMethod
     *
     * @return bool
     */
    private function isBancontactWithQRCodePaymentMethod(MolPaymentMethod $paymentMethod): bool
    {
        $isBancontactMethod = PaymentMethod::BANCONTACT === $paymentMethod->getPaymentMethodName();
        $isBancontactQRCodeEnabled = Configuration::get(Config::MOLLIE_BANCONTACT_QR_CODE_ENABLED);
        $isPaymentApi = $paymentMethod->method === Config::MOLLIE_PAYMENTS_API;

        return $isBancontactMethod && $isBancontactQRCodeEnabled && $isPaymentApi;
    }

    private function isIFrame()
    {
        if (!Configuration::get(Config::MOLLIE_IFRAME)) {
            return false;
        }

        return true;
    }

    private function isSingleClick()
    {
        if (!Configuration::get(Config::MOLLIE_SINGLE_CLICK_PAYMENT)) {
            return false;
        }

        return true;
    }
}
