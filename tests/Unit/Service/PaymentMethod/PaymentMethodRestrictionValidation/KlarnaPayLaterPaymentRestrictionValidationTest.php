<?php

use Mollie\Config\Config;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidation\KlarnaPayLaterPaymentMethodRestrictionValidator;
use Mollie\Tests\Unit\Tools\UnitTestCase;

class KlarnaPayLaterPaymentRestrictionValidationTest extends UnitTestCase
{
	/**
	 * @dataProvider getKlarnaPayLaterPaymentRestrictionValidationDataProvider
	 */
	public function testIsValid($context, $paymentMethodCountryProvider, $expectedResult)
	{
		$klarnaPayLaterValidation = new KlarnaPayLaterPaymentMethodRestrictionValidator(
			$context,
			$paymentMethodCountryProvider
		);

		$isValid = $klarnaPayLaterValidation->isValid(
			$this->mockPaymentMethod(Config::MOLLIE_METHOD_ID_KLARNA_PAY_LATER, true)
		);
		$this->assertEquals($expectedResult, $isValid);
	}

	public function getKlarnaPayLaterPaymentRestrictionValidationDataProvider()
	{
		return [
			'All checks pass' => [
				'context' => $this->mockContext('AT', 'AUD'),
				'paymentMethodCountryProvider' => $this->mockPaymentMethodCountryProvider([
					'at',
				]),
				'expectedResult' => true,
			],
			'Payment method country is NOT in allowed list' => [
				'context' => $this->mockContext('LT', 'AUD'),
				'paymentMethodCountryProvider' => $this->mockPaymentMethodCountryProvider([
					'at',
				]),
				'expectedResult' => false,
			],
			'Payment method country allowed list is empty' => [
				'context' => $this->mockContext('LT', 'AUD'),
				'paymentMethodCountryProvider' => $this->mockPaymentMethodCountryProvider(
					null
				),
				'expectedResult' => true,
			],
			'Payment method country allowed list is different case' => [
				'context' => $this->mockContext('LT', 'AUD'),
				'paymentMethodCountryProvider' => $this->mockPaymentMethodCountryProvider([
					'AT', 'De', 'lt',
				]),
				'expectedResult' => true,
			],
		];
	}

	/**
	 * @dataProvider getKlarnaPayLaterPaymentRestrictionSupportedDataProvider
	 */
	public function testIsSupported($context, $paymentMethodCountryProvider, $paymentName, $expectedResult)
	{
		$klarnaValidation = new KlarnaPayLaterPaymentMethodRestrictionValidator(
			$context,
			$paymentMethodCountryProvider
		);
		$this->assertEquals($expectedResult, $klarnaValidation->supports($this->mockPaymentMethod($paymentName, true)));
	}

	public function getKlarnaPayLaterPaymentRestrictionSupportedDataProvider()
	{
		return [
			'Supported' => [
				'context' => $this->mockContext('AT', 'AUD'),
				'paymentMethodCountryProvider' => $this->mockPaymentMethodCountryProvider([
					'nl', 'de', 'at', 'fi',
				]),
				'paymentName' => Config::MOLLIE_METHOD_ID_KLARNA_PAY_LATER,
				'expectedResult' => true,
			],
			'Not supported' => [
				'context' => $this->mockContext('AT', 'AUD'),
				'paymentMethodCountryProvider' => $this->mockPaymentMethodCountryProvider([
					'nl', 'de', 'at', 'fi',
				]),
				'paymentName' => Config::MOLLIE_METHOD_ID_APPLE_PAY,
				'expectedResult' => false,
			],
		];
	}
}
