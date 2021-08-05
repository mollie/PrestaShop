<?php

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Tests\Unit\Tools\UnitTestCase;
use Mollie\Validator\NewOrderMailValidator;
use PHPUnit\Framework\MockObject\MockObject;

class NewOrderMailValidatorTest extends UnitTestCase
{
    /**
     * @var ConfigurationAdapter|MockObject
     */
    private $configurationAdapter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configurationAdapter = $this
            ->getMockBuilder(ConfigurationAdapter::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    /** @dataProvider getCanNewOrderMailBeSentData */
    public function testCanNewOrderMailBeSent($orderStateId, $sendNewOrder, $paidOrderState, $outOfStockOrderState, $expected)
    {
        $this->configurationAdapter->expects($this->any())->method('get')
            ->withConsecutive([Config::MOLLIE_SEND_NEW_ORDER], [Config::MOLLIE_STATUS_PAID], [Config::STATUS_PS_OS_OUTOFSTOCK_PAID])
            ->willReturnOnConsecutiveCalls($sendNewOrder, $paidOrderState, $outOfStockOrderState);

        $newOrderMailValidator = new NewOrderMailValidator($this->configurationAdapter);
        $result = $newOrderMailValidator->validate($orderStateId);

        $this->assertEquals($expected, $result);
    }

    public function getCanNewOrderMailBeSentData()
    {
        return [
            'Send on paid because orderStateId is same as paidOrderState and send email on paid is enabled' => [
                'orderStateId' => 55,
                'sendNewOrder' => Config::NEW_ORDER_MAIL_SEND_ON_PAID,
                'paidOrderState' => 55,
                'outOfStockOrderState' => 60,
                'expected' => true,
            ],
            'Send on paid because orderStateId is same as outOfStockOrderState and send email on paid is enabled' => [
                'orderStateId' => 60,
                'sendNewOrder' => Config::NEW_ORDER_MAIL_SEND_ON_PAID,
                'paidOrderState' => 55,
                'outOfStockOrderState' => 60,
                'expected' => true,
            ],
            'Do not send on paid because orderStateId is not paid/out of stock' => [
                'orderStateId' => 5,
                'sendNewOrder' => Config::NEW_ORDER_MAIL_SEND_ON_PAID,
                'paidOrderState' => 55,
                'outOfStockOrderState' => 60,
                'expected' => false,
            ],
            'Send email is on never' => [
                'orderStateId' => 55,
                'sendNewOrder' => Config::NEW_ORDER_MAIL_SEND_ON_NEVER,
                'paidOrderState' => 55,
                'outOfStockOrderState' => 60,
                'expected' => false,
            ],
            'Given sendNewOrder is not viable to be sent' => [
                'orderStateId' => 55,
                'sendNewOrder' => 10,
                'paidOrderState' => 55,
                'outOfStockOrderState' => 60,
                'expected' => false,
            ],
            'sendNewOrder is not defined' => [
                'orderStateId' => 55,
                'sendNewOrder' => false,
                'paidOrderState' => 55,
                'outOfStockOrderState' => 60,
                'expected' => true,
            ],
        ];
    }
}
