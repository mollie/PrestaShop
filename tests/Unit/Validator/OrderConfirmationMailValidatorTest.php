<?php

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Tests\Unit\Tools\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class OrderConfirmationMailValidatorTest extends UnitTestCase
{
    /**
     * @var ConfigurationAdapter|MockObject
     */
    private $configurationAdapter;

    protected function setUp()
    {
        parent::setUp();

        $this->configurationAdapter = $this
            ->getMockBuilder(ConfigurationAdapter::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    /** @dataProvider getCanOrderConfirmationMailBeSentData */
    public function testCanOrderConfirmationMailBeSent($orderStateId, $sendOrderConfirmation, $paidOrderState, $outOfStockOrderState, $expected)
    {
        $this->configurationAdapter->expects($this->any())->method('get')
            ->withConsecutive([Config::MOLLIE_SEND_ORDER_CONFIRMATION], [Config::MOLLIE_STATUS_PAID], [Config::STATUS_PS_OS_OUTOFSTOCK_PAID])
            ->willReturnOnConsecutiveCalls($sendOrderConfirmation, $paidOrderState, $outOfStockOrderState);

        $orderConfMailValidator = new \Mollie\Validator\OrderConfMailValidator($this->configurationAdapter);
        $result = $orderConfMailValidator->validate($orderStateId);

        $this->assertEquals($expected, $result);
    }

    public function getCanOrderConfirmationMailBeSentData()
    {
        return [

            'sendOrderConfirmation is not defined' => [
                'orderStateId' => 55,
                'sendOrderConfirmation' => null,
                'paidOrderState' => 55,
                'outOfStockOrderState' => 60,
                'expected' => true,
            ],
        ];
    }
}
