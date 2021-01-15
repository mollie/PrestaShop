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
use Mollie\Provider\OrderTotalProviderInterface;
use Mollie\Provider\PaymentFeeProviderInterface;
use Mollie\Service\LanguageService;
use MolPaymentMethod;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Tools;

class CreditCardPaymentOptionProvider implements PaymentOptionProviderInterface
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
	 * @var OrderTotalProviderInterface
	 */
	private $orderTotalProvider;

	/**
	 * @var PaymentFeeProviderInterface
	 */
	private $paymentFeeProvider;

	/**
	 * @var LanguageService
	 */
	private $languageService;

	public function __construct(
		Mollie $module,
		LegacyContext $context,
		CreditCardLogoProvider $creditCardLogoProvider,
		OrderTotalProviderInterface $orderTotalProvider,
		PaymentFeeProviderInterface $paymentFeeProvider,
		LanguageService $languageService
	) {
		$this->module = $module;
		$this->context = $context;
		$this->creditCardLogoProvider = $creditCardLogoProvider;
		$this->orderTotalProvider = $orderTotalProvider;
		$this->paymentFeeProvider = $paymentFeeProvider;
		$this->languageService = $languageService;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPaymentOption(MolPaymentMethod $paymentMethod)
	{
		$paymentOption = new PaymentOption();
		$paymentOption->setCallToActionText(
			$paymentMethod->title ?:
				$this->languageService->lang($paymentMethod->method_name)
		);
		$paymentOption->setModuleName($this->module->name);
		$paymentOption->setAction($this->context->getLink()->getModuleLink(
			'mollie',
			'payScreen',
			['method' => $paymentMethod->getPaymentMethodName(), 'rand' => Mollie\Utility\TimeUtility::getCurrentTimeStamp(), 'cardToken' => ''],
			true
		));
		$paymentOption->setInputs([
			[
				'type' => 'hidden',
				'name' => "mollieCardToken{$paymentMethod->getPaymentMethodName()}",
				'value' => '',
			],
		]);

		$this->context->getSmarty()->assign([
			'mollieIFrameJS' => 'https://js.mollie.com/v1/mollie.js',
			'price' => $this->orderTotalProvider->getOrderTotal(),
			'priceSign' => $this->context->getCurrencySign(),
			'methodId' => $paymentMethod->getPaymentMethodName(),
		]);
		$paymentOption->setLogo($this->creditCardLogoProvider->getMethodOptionLogo($paymentMethod));

		$paymentOption->setAdditionalInformation($this->module->display(
			$this->module->getPathUri(), 'views/templates/hook/mollie_iframe.tpl'
		));

		$paymentOption->setInputs([
			[
				'type' => 'hidden',
				'name' => "mollieCardToken{$paymentMethod->getPaymentMethodName()}",
				'value' => '',
			],
		]);
		$paymentFee = $this->paymentFeeProvider->getPaymentFee($paymentMethod);

		if ($paymentFee) {
			$paymentOption->setInputs([
				[
					'type' => 'hidden',
					'name' => "mollieCardToken{$paymentMethod->getPaymentMethodName()}",
					'value' => '',
				],
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
			]);
		}

		return $paymentOption;
	}
}
