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

namespace Mollie\Provider\PaymentOption;

use Mollie;
use Mollie\Adapter\LegacyContext;
use Mollie\Provider\CreditCardLogoProvider;
use Mollie\Provider\PaymentFeeProviderInterface;
use MolPaymentMethod;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Tools;

class BasePaymentOptionProvider implements PaymentOptionProviderInterface
{
	/**
	 * @var Mollie
	 */
	private $module;

	/**
	 * @var LegacyContext
	 */
	private $context;

	/**
	 * @var CreditCardLogoProvider
	 */
	private $creditCardLogoProvider;

	/**
	 * @var PaymentFeeProviderInterface
	 */
	private $paymentFeeProvider;

	public function __construct(
		Mollie $module,
		LegacyContext $context,
		CreditCardLogoProvider $creditCardLogoProvider,
		PaymentFeeProviderInterface $paymentFeeProvider
	) {
		$this->module = $module;
		$this->context = $context;
		$this->creditCardLogoProvider = $creditCardLogoProvider;
		$this->paymentFeeProvider = $paymentFeeProvider;
	}

	/**
	 * {@inheritDoc}
	 */
	public function providePaymentOption(MolPaymentMethod $paymentMethod)
	{
		$paymentOption = new PaymentOption();
		$paymentOption->setCallToActionText($this->module->l($paymentMethod->method_name));
		$paymentOption->setModuleName($this->module->name);
		$paymentOption->setAction($this->context->getLink()->getModuleLink(
			'mollie',
			'payment',
			['method' => $paymentMethod->getPaymentMethodName(), 'rand' => Mollie\Utility\TimeUtility::getCurrentTimeStamp()],
			true
		));
		$paymentOption->setLogo($this->creditCardLogoProvider->getMethodOptionLogo($paymentMethod));
		$paymentFee = $this->paymentFeeProvider->providePaymentFee($paymentMethod);

		if ($paymentFee) {
			$paymentOption->setInputs(
				[
					[
						'type' => 'hidden',
						'name' => 'payment-fee-price',
						'value' => $paymentFee,
					],
					[
						'type' => 'hidden',
						'name' => 'payment-fee-price-display',
						'value' => sprintf($this->module->l('Payment Fee: %1s'), Tools::displayPrice($paymentFee)),
					],
				]
			);
		}

		return $paymentOption;
	}
}
