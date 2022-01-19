<?php

use Mollie\Config\Config;
use Mollie\Enum\PaymentTypeEnum;
use Mollie\Exception\ShipmentCannotBeSentException;
use PHPUnit\Framework\TestCase;

class CanSendShipmentTest extends TestCase
{
    /**
     * @var \Mollie\Adapter\ConfigurationAdapter|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configurationAdapter;

    /**
     * @var \Mollie\Provider\Shipment\AutomaticShipmentSenderStatusesProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    private $automaticShipmentSenderStatusesProvider;

    /**
     * @var \Mollie\Handler\Api\OrderEndpointPaymentTypeHandler|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderEndpointPaymentTypeHandler;

    /**
     * @var \Mollie\Repository\PaymentMethodRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMethodRepository;

    /**
     * @var \Mollie\Service\ShipmentService|\PHPUnit\Framework\MockObject\MockObject
     */
    private $shipmentService;

    /**
     * @var Order|\PHPUnit\Framework\MockObject\MockObject
     */
    private $order;

    /**
     * @var OrderState|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderState;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configurationAdapter = $this
            ->getMockBuilder(\Mollie\Adapter\ConfigurationAdapter::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->automaticShipmentSenderStatusesProvider = $this
            ->getMockBuilder(\Mollie\Provider\Shipment\AutomaticShipmentSenderStatusesProvider::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->orderEndpointPaymentTypeHandler = $this
            ->getMockBuilder(\Mollie\Handler\Api\OrderEndpointPaymentTypeHandler::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->paymentMethodRepository = $this
            ->getMockBuilder(\Mollie\Repository\PaymentMethodRepository::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->shipmentService = $this
            ->getMockBuilder(\Mollie\Service\ShipmentService::class)
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
    }

    /** @dataProvider getSendShipmentVerificationData */
    public function testCanSendShipment(
        $shipmentInformation,
        $configuration,
        $automaticShipmentSenderStatuses,
        $paymentInformation,
        $paymentType,
        $exception,
        $expected
    ) {
        $this->order->id = 1;
        $this->order->reference = 'test';
        $this->orderState->id = 1;

        $this->shipmentService
            ->expects($this->any())
            ->method('getShipmentInformation')
            ->willReturn($shipmentInformation)
        ;

        foreach ($configuration as $key => $value) {
            $this->configurationAdapter
                ->expects($this->any())
                ->method('get')
                ->with($key)
                ->willReturn($value)
            ;
        }

        $this->automaticShipmentSenderStatusesProvider
            ->expects($this->any())
            ->method('getAutomaticShipmentSenderStatuses')
            ->willReturn($automaticShipmentSenderStatuses)
        ;

        $this->paymentMethodRepository
            ->expects($this->any())
            ->method('getPaymentBy')
            ->willReturn($paymentInformation)
        ;

        $this->orderEndpointPaymentTypeHandler
            ->expects($this->any())
            ->method('getPaymentTypeFromTransactionId')
            ->willReturn($paymentType)
        ;

        $canSendShipment = new \Mollie\Verification\Shipment\CanSendShipment(
            $this->configurationAdapter,
            $this->automaticShipmentSenderStatusesProvider,
            $this->orderEndpointPaymentTypeHandler,
            $this->paymentMethodRepository,
            $this->shipmentService
        );

        if ($exception) {
            $this->expectException($exception['class']);
            $this->expectExceptionCode($exception['code']);
        }
        $result = $canSendShipment->verify($this->order, $this->orderState);

        $this->assertEquals($expected, $result);
    }

    public function getSendShipmentVerificationData()
    {
        return [
            'All checks pass' => [
                'shipmentInformation' => [
                    'testData' => 'testData',
                ],
                'configuration' => [
                    Config::MOLLIE_AUTO_SHIP_MAIN => true, //isAutomaticShipmentInformationSenderEnabled
                ],
                'automaticShipmentSenderStatuses' => [0, 1, 2, 3],
                'paymentInformation' => [
                    'transaction_id' => 'test',
                ],
                'paymentType' => PaymentTypeEnum::PAYMENT_TYPE_ORDER,
                'exception' => [],
                'expected' => true,
            ],
            'Has no shipping information' => [
                'shipmentInformation' => [],
                'configuration' => [
                    Config::MOLLIE_AUTO_SHIP_MAIN => true, //isAutomaticShipmentInformationSenderEnabled
                ],
                'automaticShipmentSenderStatuses' => [0, 1, 2, 3],
                'paymentInformation' => [
                    'transaction_id' => 'test',
                ],
                'paymentType' => PaymentTypeEnum::PAYMENT_TYPE_ORDER,
                'exception' => [],
                'expected' => true,
            ],
            'Automatic shipment information sender is disabled' => [
                'shipmentInformation' => [
                    'testData' => 'testData',
                ],
                'configuration' => [
                    Config::MOLLIE_AUTO_SHIP_MAIN => false, //isAutomaticShipmentInformationSenderEnabled
                ],
                'automaticShipmentSenderStatuses' => [0, 1, 2, 3],
                'paymentInformation' => [
                    'transaction_id' => 'test',
                ],
                'paymentType' => PaymentTypeEnum::PAYMENT_TYPE_ORDER,
                'exception' => [
                    'class' => ShipmentCannotBeSentException::class,
                    'code' => ShipmentCannotBeSentException::AUTOMATIC_SHIPMENT_SENDER_IS_NOT_AVAILABLE,
                ],
                'expected' => null,
            ],
            'Order state is not in automatic shipment sender list' => [
                'shipmentInformation' => [
                    'testData' => 'testData',
                ],
                'configuration' => [
                    Config::MOLLIE_AUTO_SHIP_MAIN => true, //isAutomaticShipmentInformationSenderEnabled
                ],
                'automaticShipmentSenderStatuses' => [0, 2, 3],
                'paymentInformation' => [
                    'transaction_id' => 'test',
                ],
                'paymentType' => PaymentTypeEnum::PAYMENT_TYPE_ORDER,
                'exception' => [
                    'class' => ShipmentCannotBeSentException::class,
                    'code' => ShipmentCannotBeSentException::AUTOMATIC_SHIPMENT_SENDER_IS_NOT_AVAILABLE,
                ],
                'expected' => null,
            ],
            'Has no payment information' => [
                'shipmentInformation' => [
                    'testData' => 'testData',
                ],
                'configuration' => [
                    Config::MOLLIE_AUTO_SHIP_MAIN => true, //isAutomaticShipmentInformationSenderEnabled
                ],
                'automaticShipmentSenderStatuses' => [0, 1, 2, 3],
                'paymentInformation' => [],
                'paymentType' => PaymentTypeEnum::PAYMENT_TYPE_ORDER,
                'exception' => [
                    'class' => ShipmentCannotBeSentException::class,
                    'code' => ShipmentCannotBeSentException::ORDER_HAS_NO_PAYMENT_INFORMATION,
                ],
                'expected' => null,
            ],
            'Has payment information but no transaction_id' => [
                'shipmentInformation' => [
                    'testData' => 'testData',
                ],
                'configuration' => [
                    Config::MOLLIE_AUTO_SHIP_MAIN => true, //isAutomaticShipmentInformationSenderEnabled
                ],
                'automaticShipmentSenderStatuses' => [0, 1, 2, 3],
                'paymentInformation' => [
                    'test' => 123,
                ],
                'paymentType' => PaymentTypeEnum::PAYMENT_TYPE_ORDER,
                'exception' => [
                    'class' => ShipmentCannotBeSentException::class,
                    'code' => ShipmentCannotBeSentException::ORDER_HAS_NO_PAYMENT_INFORMATION,
                ],
                'expected' => null,
            ],
            'Is payment api' => [
                'shipmentInformation' => [
                    'testData' => 'testData',
                ],
                'configuration' => [
                    Config::MOLLIE_AUTO_SHIP_MAIN => true, //isAutomaticShipmentInformationSenderEnabled
                ],
                'automaticShipmentSenderStatuses' => [0, 1, 2, 3],
                'paymentInformation' => [
                    'transaction_id' => 'test',
                ],
                'paymentType' => PaymentTypeEnum::PAYMENT_TYPE_PAYMENT,
                'exception' => [
                    'class' => ShipmentCannotBeSentException::class,
                    'code' => ShipmentCannotBeSentException::PAYMENT_IS_NOT_ORDER,
                ],
                'expected' => null,
            ],
        ];
    }
}
