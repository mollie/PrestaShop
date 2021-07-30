<?php

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Tests\Unit\Tools\UnitTestCase;
use Mollie\Validator\NewOrderMailValidator;
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
            ->withConsecutive([Config::MOLLIE_SEND_NEW_ORDER], [Config::MOLLIE_STATUS_PAID], [Config::STATUS_PS_OS_OUTOFSTOCK_PAID])
            ->willReturnOnConsecutiveCalls($sendOrderConfirmation, $paidOrderState, $outOfStockOrderState);

        $newOrderMailValidator = new NewOrderMailValidator($this->configurationAdapter);
        $result = $newOrderMailValidator->validate($orderStateId);

        $this->assertEquals($expected, $result);
    }

    public function getCanOrderConfirmationMailBeSentData()
    {
        return [
            'Send on created' => [
                'orderStateId' => 17,
                'sendOrderConfirmation' => Config::ORDER_CONF_MAIL_SEND_ON_CREATION,
                'paidOrderState' => 55,
                'outOfStockOrderState' => 60,
                'expected' => true,
            ],
            'Send on paid because orderStateId is same as paidOrderState and send email on paid is enabled' => [
                'orderStateId' => 55,
                'sendOrderConfirmation' => Config::ORDER_CONF_MAIL_SEND_ON_PAID,
                'paidOrderState' => 55,
                'outOfStockOrderState' => 60,
                'expected' => true,
            ],
            'Send on paid because orderStateId is same as outOfStockOrderState and send email on paid is enabled' => [
                'orderStateId' => 60,
                'sendOrderConfirmation' => Config::ORDER_CONF_MAIL_SEND_ON_PAID,
                'paidOrderState' => 55,
                'outOfStockOrderState' => 60,
                'expected' => true,
            ],
            'Do not send on paid because orderStateId is not paid/out of stock' => [
                'orderStateId' => 5,
                'sendOrderConfirmation' => Config::ORDER_CONF_MAIL_SEND_ON_PAID,
                'paidOrderState' => 55,
                'outOfStockOrderState' => 60,
                'expected' => false,
            ],
            'Send email is on never' => [
                'orderStateId' => 55,
                'sendOrderConfirmation' => Config::NEW_ORDER_MAIL_SEND_ON_NEVER,
                'paidOrderState' => 55,
                'outOfStockOrderState' => 60,
                'expected' => false,
            ],
            'Given sendNewOrder is not viable to be sent' => [
                'orderStateId' => 55,
                'sendOrderConfirmation' => 10,
                'paidOrderState' => 55,
                'outOfStockOrderState' => 60,
                'expected' => false,
            ],
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
