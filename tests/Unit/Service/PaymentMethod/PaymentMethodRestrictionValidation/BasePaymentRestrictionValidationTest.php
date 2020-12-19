<?php

use Mollie\Config\Config;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidation\BasePaymentMethodRestrictionValidator;
use Mollie\Tests\Unit\Tools\UnitTestCase;

class BasePaymentRestrictionValidationTest extends UnitTestCase
{
	/**
	 * @dataProvider getBasePaymentRestrictionValidationDataProvider
	 */
	public function testIsValid(
		$paymentMethod,
		$context,
		$paymentMethodCurrencyProvider,
		$expectedResult
	) {
		$basePaymentRestrictionValidation = new BasePaymentMethodRestrictionValidator(
			$context,
			$paymentMethodCurrencyProvider
		);
		$this->assertEquals($expectedResult, $basePaymentRestrictionValidation->isValid($paymentMethod));
	}

	public function getBasePaymentRestrictionValidationDataProvider()
	{
		return [
			'All checks pass' => [
				'paymentMethod' => $this->mockPaymentMethod(Config::CARTES_BANCAIRES, true),
				'context' => $this->mockContext('AT', 'AUD'),
				'paymentMethodCurrencyProvider' => $this->mockPaymentMethodCurrencyProvider([
					'aud', 'bgn', 'eur',
				]),
				'expectedResult' => true,
			],
			'Payment method is not enabled' => [
				'paymentMethod' => $this->mockPaymentMethod(Config::CARTES_BANCAIRES, false),
				'context' => $this->mockContext('AT', 'AUD'),
				'paymentMethodCurrencyProvider' => $this->mockPaymentMethodCurrencyProvider([
					'aud', 'bgn', 'eur',
				]),
				'expectedResult' => false,
			],
			'Available currency option list is not defined' => [
				'paymentMethod' => $this->mockPaymentMethod(Config::CARTES_BANCAIRES, true),
				'context' => $this->mockContext('AT', 'AUD'),
				'paymentMethodCurrencyProvider' => $this->mockPaymentMethodCurrencyProvider(null),
				'expectedResult' => false,
			],
			'Currency is not in available currencies' => [
				'paymentMethod' => $this->mockPaymentMethod(Config::CARTES_BANCAIRES, true),
				'context' => $this->mockContext('AT', 'AUD'),
				'paymentMethodCurrencyProvider' => $this->mockPaymentMethodCurrencyProvider([
					'bgn', 'eur',
				]),
				'expectedResult' => false,
			],
		];
	}
}
