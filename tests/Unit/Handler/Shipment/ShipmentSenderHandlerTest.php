<?php

use Mollie\Exception\ShipmentCannotBeSentException;
use Mollie\Handler\Shipment\ShipmentSenderHandler;
use Mollie\Logger\PrestaLogger;
use Mollie\Service\ExceptionService;
use Mollie\Service\Shipment\ShipmentInformationSender;
use Mollie\Verification\Shipment\CanSendShipment;
use \Mollie\Api\MollieApiClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ShipmentSenderHandlerTest extends TestCase
{
	/**
	 * @var MollieApiClient|MockObject
	 */
	private $apiClient;

	/**
	 * @var Order|MockObject
	 */
	private $order;

	/**
	 * @var OrderState|MockObject
	 */
	private $orderState;

	/**
	 * @var CanSendShipment|MockObject
	 */
	private $canSendShipment;

	/**
	 * @var ShipmentInformationSender|MockObject
	 */
	private $shipmentInformationSender;

	/**
	 * @var ExceptionService|MockObject
	 */
	private $exceptionService;

	/**
	 * @var PrestaLogger|MockObject
	 */
	private $moduleLogger;

	protected function setUp(): void
	{
		parent::setUp();

		$this->apiClient = $this
			->getMockBuilder(MollieApiClient::class)
			->disableOriginalConstructor()
			->getMock()
		;

		$this->order = $this
			->getMockBuilder(Order::class)
			->disableOriginalConstructor()
			->getMock()
		;

		$this->orderState = $this
			->getMockBuilder(OrderState::class)
			->disableOriginalConstructor()
			->getMock()
		;

		$this->canSendShipment = $this
			->getMockBuilder(CanSendShipment::class)
			->disableOriginalConstructor()
			->getMock()
		;

		$this->shipmentInformationSender = $this
			->getMockBuilder(ShipmentInformationSender::class)
			->disableOriginalConstructor()
			->getMock()
		;

		$this->exceptionService = $this
			->getMockBuilder(ExceptionService::class)
			->disableOriginalConstructor()
			->getMock()
		;

		$this->moduleLogger = $this
			->getMockBuilder(PrestaLogger::class)
			->disableOriginalConstructor()
			->getMock()
		;
	}

	public function testCanSendShipment()
	{
		$this->canSendShipment
			->expects($this->once())
			->method('verify')
			->willReturn(true)
		;

		$this->exceptionService
			->expects($this->never())
			->method('getErrorMessages')
			->willReturn([])
		;

		$this->exceptionService
			->expects($this->never())
			->method('getErrorMessageForException')
		;

		$this->moduleLogger
			->expects($this->never())
			->method('error')
		;

		$shipmentSenderHandler = new ShipmentSenderHandler(
			$this->canSendShipment,
			$this->shipmentInformationSender,
			$this->exceptionService,
			$this->moduleLogger
		);
		$result = $shipmentSenderHandler->handleShipmentSender($this->apiClient, $this->order, $this->orderState);

		$this->assertEquals(true, $result);
	}

	public function testOnVerificationExceptionLogExceptionAndNotSendInformation()
	{
		$this->order->reference = 'test';

		$this->canSendShipment
			->expects($this->once())
			->method('verify')
			->willThrowException(new ShipmentCannotBeSentException(
				'Shipment information cannot be sent. No shipment information found by order reference',
				ShipmentCannotBeSentException::NO_SHIPPING_INFORMATION,
				$this->order->reference
			))
		;

		$this->exceptionService
			->expects($this->once())
			->method('getErrorMessages')
			->willReturn([])
		;

		$this->exceptionService
			->expects($this->once())
			->method('getErrorMessageForException')
		;

		$this->moduleLogger
			->expects($this->once())
			->method('error')
		;

		$shipmentSenderHandler = new ShipmentSenderHandler(
			$this->canSendShipment,
			$this->shipmentInformationSender,
			$this->exceptionService,
			$this->moduleLogger
		);
		$result = $shipmentSenderHandler->handleShipmentSender($this->apiClient, $this->order, $this->orderState);

		$this->assertEquals(false, $result);
	}
}
