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
		$orderTotalService,
		$orderTotalProvider,
		$orderTotalRepository,
		$expectedResult
	) {
		$basePaymentRestrictionValidation = new BasePaymentMethodRestrictionValidator(
			$context,
			$orderTotalService,
			$orderTotalProvider,
            $orderTotalRepository
		);
		$this->assertEquals($expectedResult, $basePaymentRestrictionValidation->isValid($paymentMethod));
	}

	public function getBasePaymentRestrictionValidationDataProvider()
	{
		return [
			'All checks pass' => [
				'paymentMethod' => $this->mockPaymentMethod(Config::CARTES_BANCAIRES, true),
				'context' => $this->mockContext('AT', 'AUD'),
				'orderTotalService' => $this->mockOrderTotalService(false, false),
				'orderTotalProvider' => $this->mockOrderTotalProvider(100),
                'orderTotalRepository' => $this->mockOrderTotalRepository(true),
                'expectedResult' => true,
			],
			'Payment method is not enabled' => [
				'paymentMethod' => $this->mockPaymentMethod(Config::CARTES_BANCAIRES, false),
				'context' => $this->mockContext('AT', 'AUD'),
				'orderTotalService' => $this->mockOrderTotalService(false, false),
				'orderTotalProvider' => $this->mockOrderTotalProvider(100),
                'orderTotalRepository' => $this->mockOrderTotalRepository(true),
                'expectedResult' => false,
			],
			'Available currency option list is not defined' => [
				'paymentMethod' => $this->mockPaymentMethod(Config::CARTES_BANCAIRES, true),
				'context' => $this->mockContext('AT', 'AUD'),
				'orderTotalService' => $this->mockOrderTotalService(false, false),
				'orderTotalProvider' => $this->mockOrderTotalProvider(100),
                'orderTotalRepository' => $this->mockOrderTotalRepository(false),
                'expectedResult' => false,
			],
			'Currency is not in available currencies' => [
				'paymentMethod' => $this->mockPaymentMethod(Config::CARTES_BANCAIRES, true),
				'context' => $this->mockContext('AT', 'AUD'),
				'orderTotalService' => $this->mockOrderTotalService(false, false),
				'orderTotalProvider' => $this->mockOrderTotalProvider(100),
                'orderTotalRepository' => $this->mockOrderTotalRepository(false),
                'expectedResult' => false,
			],
			'Order total is lower than minimum' => [
				'paymentMethod' => $this->mockPaymentMethod(Config::CARTES_BANCAIRES, true),
				'context' => $this->mockContext('AT', 'AUD'),
				'orderTotalService' => $this->mockOrderTotalService(false, true),
				'orderTotalProvider' => $this->mockOrderTotalProvider(100),
                'orderTotalRepository' => $this->mockOrderTotalRepository(true),
                'expectedResult' => false,
			],
			'Order total is higher than maximum' => [
				'paymentMethod' => $this->mockPaymentMethod(Config::CARTES_BANCAIRES, true),
				'context' => $this->mockContext('AT', 'AUD'),
				'orderTotalService' => $this->mockOrderTotalService(true, false),
				'orderTotalProvider' => $this->mockOrderTotalProvider(100),
				'orderTotalRepository' => $this->mockOrderTotalRepository(true),
				'expectedResult' => false,
			],
		];
	}
}
