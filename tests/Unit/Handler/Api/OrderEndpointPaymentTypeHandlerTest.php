<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

use Mollie\Enum\PaymentTypeEnum;
use Mollie\Handler\Api\OrderEndpointPaymentTypeHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrderEndpointPaymentTypeHandlerTest extends TestCase
{
    /**
     * @var \Mollie\Verification\PaymentType\CanBeRegularPaymentType|MockObject
     */
    private $canBeRegularPaymentType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->canBeRegularPaymentType = $this
            ->getMockBuilder(\Mollie\Verification\PaymentType\CanBeRegularPaymentType::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    public function testPaymentTypeNotFoundByDefault()
    {
        $this->canBeRegularPaymentType
            ->expects($this->once())
            ->method('verify')
            ->willReturn(false)
        ;

        $orderEndpointPaymentTypeHandler = new OrderEndpointPaymentTypeHandler($this->canBeRegularPaymentType);
        $result = $orderEndpointPaymentTypeHandler->getPaymentTypeFromTransactionId('test');

        $this->assertEquals(PaymentTypeEnum::PAYMENT_TYPE_PAYMENT, $result);
    }

    public function testPaymentTypeRegular()
    {
        $this->canBeRegularPaymentType
            ->expects($this->once())
            ->method('verify')
            ->willReturn(true)
        ;

        $orderEndpointPaymentTypeHandler = new OrderEndpointPaymentTypeHandler($this->canBeRegularPaymentType);
        $result = $orderEndpointPaymentTypeHandler->getPaymentTypeFromTransactionId('test');

        $this->assertEquals(PaymentTypeEnum::PAYMENT_TYPE_ORDER, $result);
    }
}
