<?php

use Mollie\Api\MollieApiClient;
use Mollie\Exception\ShipmentCannotBeSentException;
use Mollie\Handler\Shipment\ShipmentSenderHandler;
use Mollie\Service\Shipment\ShipmentInformationSender;
use Mollie\Verification\Shipment\CanSendShipment;
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

    protected function setUp()
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
    }

    public function testCanSendShipment(): void
    {
        $this->canSendShipment
            ->expects($this->once())
            ->method('verify')
            ->willReturn(true)
        ;

        $shipmentSenderHandler = new ShipmentSenderHandler(
            $this->canSendShipment,
            $this->shipmentInformationSender
        );

        $shipmentSenderHandler->handleShipmentSender($this->apiClient, $this->order, $this->orderState);
    }

    public function testItSuccessfullyFailsToSendShipmentExceptionThrown(): void
    {
        $this->order->reference = 'test';

        $this->canSendShipment
            ->expects($this->once())
            ->method('verify')
            ->willThrowException(new ShipmentCannotBeSentException(
                '',
                ShipmentCannotBeSentException::ORDER_HAS_NO_PAYMENT_INFORMATION,
                $this->order->reference
            ))
        ;

        $shipmentSenderHandler = new ShipmentSenderHandler(
            $this->canSendShipment,
            $this->shipmentInformationSender
        );

        $this->expectException(ShipmentCannotBeSentException::class);
        $this->expectExceptionCode(ShipmentCannotBeSentException::ORDER_HAS_NO_PAYMENT_INFORMATION);

        $shipmentSenderHandler->handleShipmentSender($this->apiClient, $this->order, $this->orderState);
    }

    public function testItSuccessfullyFailsToSendShipmentVerificationReturnedFalse(): void
    {
        $this->canSendShipment
            ->expects($this->once())
            ->method('verify')
            ->willReturn(false)
        ;

        $this->shipmentInformationSender
            ->expects($this->never())
            ->method('sendShipmentInformation')
        ;

        $shipmentSenderHandler = new ShipmentSenderHandler(
            $this->canSendShipment,
            $this->shipmentInformationSender
        );

        $shipmentSenderHandler->handleShipmentSender($this->apiClient, $this->order, $this->orderState);
    }
}
