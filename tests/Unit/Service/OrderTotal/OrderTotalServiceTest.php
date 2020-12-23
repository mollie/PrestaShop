<?php

use Mollie\Config\Config;
use Mollie\Service\OrderTotal\OrderTotalService;
use Mollie\Tests\Unit\Tools\UnitTestCase;

class OrderTotalServiceTest extends UnitTestCase
{
	/**
	 * @dataProvider isOrderTotalLowerThanMinimumAllowedDataProvider
	 */
	public function testIsOrderTotalLowerThanMinimumAllowed($paymentMethod, $orderTotal, $minimumValue, $maximumValue, $expectedResult)
	{
		$orderTotalService = new OrderTotalService(
			$this->mockContext('AT', 'AUD'),
			$this->mockOrderTotalRestrictionProvider($minimumValue, $maximumValue)
		);

		$isOrderTotalLower = $orderTotalService->isOrderTotalLowerThanMinimumAllowed($paymentMethod, $orderTotal);
		$this->assertEquals($expectedResult, $isOrderTotalLower);
	}

	public function isOrderTotalLowerThanMinimumAllowedDataProvider()
	{
		return [
			'Not lower than minimum allowed' => [
				'paymentMethod' => $this->mockPaymentMethod(Config::MOLLIE_METHOD_ID_KLARNA_SLICE_IT, true),
				'orderTotal' => 100.01,
				'minimumAmount' => 10.00,
				'maximumAmount' => 200.00,
				'expectedValue' => false,
			],
			'Lower than minimum allowed' => [
				'paymentMethod' => $this->mockPaymentMethod(Config::MOLLIE_METHOD_ID_KLARNA_SLICE_IT, true),
				'orderTotal' => 5.01,
				'minimumAmount' => 10.00,
				'maximumAmount' => 200.00,
				'expectedValue' => true,
			],
			'Can accept non float values' => [
				'paymentMethod' => $this->mockPaymentMethod(Config::MOLLIE_METHOD_ID_APPLE_PAY, true),
				'orderTotal' => 10,
				'minimumAmount' => 10,
				'maximumAmount' => 200,
				'expectedValue' => false,
			],
		];
	}

	/**
	 * @dataProvider isOrderTotalHigherThanMaximumAllowedDataProvider
	 */
	public function testIsOrderTotalHigherThanMaximumAllowed($paymentMethod, $orderTotal, $minimumValue, $maximumValue, $expectedResult)
	{
		$orderTotalService = new OrderTotalService(
			$this->mockContext('AT', 'AUD'),
			$this->mockOrderTotalRestrictionProvider($minimumValue, $maximumValue)
		);

		$isOrderTotalHigher = $orderTotalService->isOrderTotalHigherThanMaximumAllowed($paymentMethod, $orderTotal);
		$this->assertEquals($expectedResult, $isOrderTotalHigher);
	}

	public function isOrderTotalHigherThanMaximumAllowedDataProvider()
	{
		return [
			'Not higher than maximum allowed' => [
				'paymentMethod' => $this->mockPaymentMethod(Config::MOLLIE_METHOD_ID_KLARNA_SLICE_IT, true),
				'orderTotal' => 100.01,
				'minimumAmount' => 10.00,
				'maximumAmount' => 200.00,
				'expectedValue' => false,
			],
			'No maximum amount is specified' => [
				'paymentMethod' => $this->mockPaymentMethod(Config::MOLLIE_METHOD_ID_KLARNA_SLICE_IT, true),
				'orderTotal' => 200.01,
				'minimumAmount' => 10.00,
				'maximumAmount' => null,
				'expectedValue' => false,
			],
			'Maximum order total amount is 0' => [
				'paymentMethod' => $this->mockPaymentMethod(Config::MOLLIE_METHOD_ID_KLARNA_SLICE_IT, true),
				'orderTotal' => 200.01,
				'minimumAmount' => 10.00,
				'maximumAmount' => 0,
				'expectedValue' => false,
			],
			'Higher than maximum allowed' => [
				'paymentMethod' => $this->mockPaymentMethod(Config::MOLLIE_METHOD_ID_APPLE_PAY, true),
				'orderTotal' => 200.01,
				'minimumAmount' => 10.00,
				'maximumAmount' => 200.00,
				'expectedValue' => true,
			],
			'Can accept non float values' => [
				'paymentMethod' => $this->mockPaymentMethod(Config::MOLLIE_METHOD_ID_APPLE_PAY, true),
				'orderTotal' => 200,
				'minimumAmount' => 10,
				'maximumAmount' => 200,
				'expectedValue' => false,
			],
		];
	}
}
