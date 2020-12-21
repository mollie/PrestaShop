<?php

use Mollie\Config\Config;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidation\ApplePayPaymentMethodRestrictionValidator;
use Mollie\Tests\Unit\Tools\UnitTestCase;

class ApplePayPaymentRestrictionValidationTest extends UnitTestCase
{
	/**
	 * @dataProvider getApplePayPaymentRestrictionValidationDataProvider
	 */
	public function testIsValid($context, $configurationAdapter, $expectedResult)
	{
		$applePayValidation = new ApplePayPaymentMethodRestrictionValidator(
			$context,
			$configurationAdapter
		);

		$isValid = $applePayValidation->isValid(
			$this->mockPaymentMethod(Config::MOLLIE_METHOD_ID_APPLE_PAY, true)
		);
		$this->assertEquals($expectedResult, $isValid);
	}

	public function getApplePayPaymentRestrictionValidationDataProvider()
	{
		return [
			'All checks pass' => [
				'context' => $this->mockContextWithCookie(1),
				'configurationAdapter' => $this->mockConfigurationAdapter([
					'PS_SSL_ENABLED_EVERYWHERE' => true,
				]),
				'expectedResult' => true,
			],
			'SSL is not enabled' => [
				'context' => $this->mockContextWithCookie(1),
				'configurationAdapter' => $this->mockConfigurationAdapter([
					'PS_SSL_ENABLED_EVERYWHERE' => false,
				]),
				'expectedResult' => false,
			],
			'Cookie has no data if Apple Pay is available' => [
				'context' => $this->mockContextWithCookie(0),
				'configurationAdapter' => $this->mockConfigurationAdapter([
					'PS_SSL_ENABLED_EVERYWHERE' => true,
				]),
				'expectedResult' => false,
			],
		];
	}

	/**
	 * @dataProvider getApplePayPaymentRestrictionSupportedDataProvider
	 */
	public function testIsSupported($paymentName, $expectedResult)
	{
		$applePayValidation = new ApplePayPaymentMethodRestrictionValidator(
			$this->mockContext('AT', 'AUD'),
			$this->mockConfigurationAdapter([
				'PS_SSL_ENABLED_EVERYWHERE' => true,
			])
		);
		$this->assertEquals($expectedResult, $applePayValidation->supports($paymentName));
	}

	public function getApplePayPaymentRestrictionSupportedDataProvider()
	{
		return [
			'Supported' => [
				'paymentMethod' => $this->mockPaymentMethod(Config::MOLLIE_METHOD_ID_APPLE_PAY, true),
				'expectedResult' => true,
			],
			'Not supported' => [
				'paymentName' => $this->mockPaymentMethod(Config::MOLLIE_METHOD_ID_KLARNA_PAY_LATER, true),
				'expectedResult' => false,
			],
		];
	}
}
