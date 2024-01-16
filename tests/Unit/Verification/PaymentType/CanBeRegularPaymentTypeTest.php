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

use PHPUnit\Framework\TestCase;

class CanBeRegularPaymentTypeTest extends TestCase
{
    /**
     * @var \Mollie\Provider\PaymentType\RegularInterfacePaymentTypeIdentification|\PHPUnit\Framework\MockObject\MockObject
     */
    private $regularPaymentTypeIdentification;

    /**
     * @var \Mollie\Adapter\ToolsAdapter|\PHPUnit\Framework\MockObject\MockObject
     */
    private $toolsAdapter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->regularPaymentTypeIdentification = $this
            ->getMockBuilder(\Mollie\Provider\PaymentType\RegularInterfacePaymentTypeIdentification::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->toolsAdapter = $this
            ->getMockBuilder(\Mollie\Adapter\ToolsAdapter::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    /** @dataProvider getCanBeRegularPaymentTypeVerificationData */
    public function testCanBeRegularPaymentTypeVerification(
        $transactionId,
        $paymentTypeIdentification,
        $paymentTypeIdentificationLength,
        $transactionIdSubstr,
        $expected
    ) {
        $this->regularPaymentTypeIdentification
            ->expects($this->any())
            ->method('getRegularPaymentIdentification')
            ->willReturn($paymentTypeIdentification)
        ;

        $this->toolsAdapter
            ->expects($this->any())
            ->method('strlen')
            ->willReturn($paymentTypeIdentificationLength)
        ;

        $this->toolsAdapter
            ->expects($this->any())
            ->method('substr')
            ->willReturn($transactionIdSubstr)
        ;

        $canBeRegularPaymentType = new \Mollie\Verification\PaymentType\CanBeRegularPaymentType(
            $this->toolsAdapter,
            $this->regularPaymentTypeIdentification
        );

        $result = $canBeRegularPaymentType->verify($transactionId);

        $this->assertEquals($expected, $result);
    }

    public function getCanBeRegularPaymentTypeVerificationData()
    {
        return [
            'Payment type regular' => [
                'transactionId' => 'testTransaction',
                'paymentTypeIdentification' => 'test',
                'paymentTypeIdentificationLength' => 4,
                'transactionIdSubstr' => 'test',
                'expected' => true,
            ],
            'No transaction id provided' => [
                'transactionId' => '',
                'paymentTypeIdentification' => 'test',
                'paymentTypeIdentificationLength' => 4,
                'transactionIdSubstr' => 'test',
                'expected' => false,
            ],
            'No regular payment identification' => [
                'transactionId' => 'testTransaction',
                'paymentTypeIdentification' => '',
                'paymentTypeIdentificationLength' => 4,
                'transactionIdSubstr' => 'test',
                'expected' => false,
            ],
            'Payment type identification length = 0' => [
                'transactionId' => 'testTransaction',
                'paymentTypeIdentification' => 'test',
                'paymentTypeIdentificationLength' => 0,
                'transactionIdSubstr' => 'test',
                'expected' => false,
            ],
            'Payment type identification not same as transaction substring' => [
                'transactionId' => 'testTransaction',
                'paymentTypeIdentification' => 'test',
                'paymentTypeIdentificationLength' => 4,
                'transactionIdSubstr' => 'different',
                'expected' => false,
            ],
        ];
    }
}
