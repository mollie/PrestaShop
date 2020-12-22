<?php

namespace Service\PaymentMethod;

use Mollie\Config\Config;
use Mollie\Exception\OrderTotalRestrictionException;
use Mollie\Provider\PaymentMethod\PaymentMethodOrderTotalRestrictionProvider;
use Mollie\Service\EntityManager\ObjectModelManager;
use Mollie\Service\PaymentMethod\PaymentMethodOrderRestrictionUpdater;
use Mollie\Tests\Unit\Tools\UnitTestCase;
use MolliePrefix\Mollie\Api\Resources\Method;
use MolPaymentMethod;

class PaymentMethodOrderRestrictionUpdaterTest extends UnitTestCase
{
	/**
	 * @var PaymentMethodOrderTotalRestrictionProvider|\PHPUnit\Framework\MockObject\MockObject
	 */
	private $paymentMethodOrderTotalRestrictionProvider;
	/**
	 * @var MolPaymentMethod|\PHPUnit\Framework\MockObject\MockObject
	 */
	private $paymentMethod;

	/**
	 * @var Method|\PHPUnit\Framework\MockObject\MockObject
	 */
	private $method;

	/**
	 * @var ObjectModelManager|\PHPUnit\Framework\MockObject\MockObject
	 */
	private $entityManager;

	protected function setUp()
	{
		$this->paymentMethodOrderTotalRestrictionProvider = $this
			->getMockBuilder(PaymentMethodOrderTotalRestrictionProvider::class)
			->disableOriginalConstructor()
			->getMock()
		;

		$this->entityManager = $this
			->getMockBuilder(ObjectModelManager::class)
			->disableOriginalConstructor()
			->getMock()
		;

		$this->paymentMethod = $this->mockPaymentMethod(Config::MOLLIE_METHOD_ID_KLARNA_PAY_LATER, true);
		$this->paymentMethod->id = 10;

		$this->method = $this
			->getMockBuilder(Method::class)
			->disableOriginalConstructor()
			->getMock()
		;
	}

	/**
	 * @dataProvider updatePaymentMethodOrderTotalRestrictionData
	 */
	public function testUpdatePaymentMethodOrderTotalRestriction(
		$method,
		$savingStatus,
		$exception,
		$expected
	) {
		$this->paymentMethodOrderTotalRestrictionProvider
			->expects($this->any())
			->method('providePaymentMethodOrderTotalRestriction')
			->willReturn($method)
		;

		$this->entityManager
			->expects($this->any())
			->method('flush')
			->willReturn(true)
		;

		$paymentMethodOrderRestrictionUpdated = new PaymentMethodOrderRestrictionUpdater(
			$this->paymentMethodOrderTotalRestrictionProvider,
			$this->entityManager
		);

		$result = $paymentMethodOrderRestrictionUpdated->updatePaymentMethodOrderTotalRestriction(
			$this->paymentMethod,
			'EUR'
		);

		$this->assertEquals($expected, $result);
	}

	public function testFailedUpdatePaymentMethodOrderTotalRestrictionOnWrongStructureOfObjectModel()
	{
		$this->paymentMethodOrderTotalRestrictionProvider
			->expects($this->any())
			->method('providePaymentMethodOrderTotalRestriction')
			->willReturn($this->mockMethodResponse())
		;

		$this->entityManager
			->expects($this->any())
			->method('flush')
			->willThrowException(new \PrestaShopException('test'))
		;

		$this->expectException(OrderTotalRestrictionException::class);
		$this->expectExceptionCode(OrderTotalRestrictionException::ORDER_TOTAL_RESTRICTION_SAVE_FAILED);

		$paymentMethodOrderRestrictionUpdated = new PaymentMethodOrderRestrictionUpdater(
			$this->paymentMethodOrderTotalRestrictionProvider,
			$this->entityManager
		);

		$result = $paymentMethodOrderRestrictionUpdated->updatePaymentMethodOrderTotalRestriction(
			$this->paymentMethod,
			'EUR'
		);

		$this->assertEquals(null, $result);
	}

	public function updatePaymentMethodOrderTotalRestrictionData()
	{
		return [
			'All checks pass' => [
				'method' => $this->mockMethodResponse(),
				'savingStatus' => true,
				'exception' => [],
				'expected' => true,
			],
			'Empty method response' => [
				'method' => null,
				'savingStatus' => true,
				'exception' => [],
				'expected' => false,
			],
		];
	}
}
