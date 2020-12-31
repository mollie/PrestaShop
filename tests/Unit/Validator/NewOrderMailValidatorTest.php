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
        $this->configurationAdapter
            ->expects($this->any())
            ->method('get')
            ->with(Config::MOLLIE_SEND_NEW_ORDER)
            ->willReturn($sendNewOrder)
        ;

        $this->configurationAdapter
            ->expects($this->any())
            ->method('get')
            ->with(Config::MOLLIE_STATUS_PAID)
            ->willReturn($paidOrderState)
        ;

        $this->configurationAdapter
            ->expects($this->any())
            ->method('get')
            ->with(Config::STATUS_PS_OS_OUTOFSTOCK_PAID)
            ->willReturn($outOfStockOrderState)
        ;

        $newOrderMailValidator = new NewOrderMailValidator($this->configurationAdapter);
        $result = $newOrderMailValidator->validate($orderStateId);

        $this->assertEquals($expected, $result);
    }

    public function getCanNewOrderMailBeSentData()
    {
        return [
            'Send on creation' => [
                'orderStateId' => 'any',
                'sendNewOrder' => Config::NEW_ORDER_MAIL_SEND_ON_CREATION,
                'paidOrderState' => 55,
                'outOfStockOrderState' => 60,
                'expected' => true,
            ],
        ];
    }
}
