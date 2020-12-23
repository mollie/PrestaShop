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

namespace Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidation;

use Mollie\Adapter\LegacyContext;
use Mollie\Config\Config;
use Mollie\Provider\PaymentMethod\PaymentMethodCountryProviderInterface;
use MolPaymentMethod;

class KlarnaPayLaterPaymentMethodRestrictionValidator implements PaymentMethodRestrictionValidatorInterface
{
	/**
	 * @var LegacyContext
	 */
	private $context;

	/**
	 * @var PaymentMethodCountryProviderInterface
	 */
	private $paymentMethodCountryProvider;

	public function __construct(
		LegacyContext $context,
		PaymentMethodCountryProviderInterface $paymentMethodCountryProvider
	) {
		$this->context = $context;
		$this->paymentMethodCountryProvider = $paymentMethodCountryProvider;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isValid(MolPaymentMethod $paymentMethod)
	{
		if (!$this->isContextCountryCodeSupported($paymentMethod)) {
			return false;
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function supports(MolPaymentMethod $paymentMethod)
	{
		return $paymentMethod->getPaymentMethodName() == Config::MOLLIE_METHOD_ID_KLARNA_PAY_LATER;
	}

	/**
	 * @param MolPaymentMethod $paymentMethod
	 *
	 * @return bool
	 */
	private function isContextCountryCodeSupported(MolPaymentMethod $paymentMethod)
	{
		if (!$this->context->getCountryIsoCode()) {
			return false;
		}
		$supportedCountries = $this->paymentMethodCountryProvider->provideAvailableCountriesByPaymentMethod($paymentMethod);

		if (!$supportedCountries) {
			return true;
		}

		return in_array(
			strtolower($this->context->getCountryIsoCode()),
			array_map('strtolower', $supportedCountries)
		);
	}
}
