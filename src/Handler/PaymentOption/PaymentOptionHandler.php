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
use Mollie\Api\Types\PaymentMethod;
use Mollie\Config\Config;
use Mollie\Provider\PaymentOption\BasePaymentOptionProvider;
use Mollie\Provider\PaymentOption\CreditCardPaymentOptionProvider;
use Mollie\Provider\PaymentOption\IdealPaymentOptionProvider;
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

	public function __construct(
		BasePaymentOptionProvider $basePaymentOptionProvider,
		CreditCardPaymentOptionProvider $creditCardPaymentOptionProvider,
		IdealPaymentOptionProvider $idealPaymentOptionProvider
	) {
		$this->basePaymentOptionProvider = $basePaymentOptionProvider;
		$this->creditCardPaymentOptionProvider = $creditCardPaymentOptionProvider;
		$this->idealPaymentOptionProvider = $idealPaymentOptionProvider;
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
			return $this->creditCardPaymentOptionProvider->getPaymentOption($paymentMethod);
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
	private function isCreditCardPaymentMethod(MolPaymentMethod $paymentMethod)
	{
		$isCreditCardPaymentMethod = PaymentMethod::CREDITCARD === $paymentMethod->getPaymentMethodName();
		$isCartesBancairesPaymentMethod = Config::CARTES_BANCAIRES === $paymentMethod->getPaymentMethodName();

		if (!$isCreditCardPaymentMethod && !$isCartesBancairesPaymentMethod) {
			return false;
		}

		if (!Configuration::get(Config::MOLLIE_IFRAME)) {
			return false;
		}

		return true;
	}
}
