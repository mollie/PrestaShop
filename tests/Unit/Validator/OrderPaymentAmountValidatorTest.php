<?php

namespace Mollie\Tests\Unit\Validator;

use Mollie\DTO\PaymentFeeData;
use Mollie\Provider\PaymentFeeProviderInterface;
use Mollie\Validator\OrderPaymentAmountValidator;
use Mollie\Validator\PaymentAmountValidator;
use MolPaymentMethod;
use PHPUnit\Framework\TestCase;

class OrderPaymentAmountValidatorTest extends TestCase
{
    /** @var PaymentFeeProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $paymentFeeProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentFeeProvider = $this->createMock(PaymentFeeProviderInterface::class);
    }

    /** @dataProvider applicableProvider */
    public function testIsApplicable(string $status, bool $isOrderSettled, bool $hasRefundsOrChargebacks, bool $expected): void
    {
        $this->assertSame($expected, $this->validator()->isApplicable($status, $isOrderSettled, $hasRefundsOrChargebacks));
    }

    public function applicableProvider(): array
    {
        return [
            'paid, order not settled' => ['paid', false, false, true],
            'authorized, order not settled' => ['authorized', false, false, true],
            'paid, order settled' => ['paid', true, false, false],
            'authorized, order settled' => ['authorized', true, false, false],
            'paid with refunds or chargebacks' => ['paid', false, true, false],
            'order shipping' => ['shipping', false, false, false],
            'order completed' => ['completed', false, false, false],
            'expired' => ['expired', false, false, false],
            'canceled' => ['canceled', false, false, false],
            'open' => ['open', false, false, false],
        ];
    }

    public function testPaymentMatchingOrderTotalIsValid(): void
    {
        $this->paymentFeeProvider->expects($this->never())->method('getPaymentFee');

        $this->assertTrue($this->validator()->validate(
            $this->apiPayment('387.84', 'EUR'),
            387.84,
            387.84,
            'EUR',
            $this->paymentMethod(),
            false
        ));
    }

    public function testPaymentMatchingCartTotalWithFeeIsValid(): void
    {
        $this->paymentFeeProvider->method('getPaymentFee')->willReturn(new PaymentFeeData(2.42, 2.0, 21.0, true));

        $this->assertTrue($this->validator()->validate(
            $this->apiPayment('102.42', 'EUR'),
            100.0,
            100.0,
            'EUR',
            $this->paymentMethod(),
            false
        ));
    }

    public function testPaymentLowerThanOrderAndCartIsInvalid(): void
    {
        $this->paymentFeeProvider->method('getPaymentFee')->willReturn(new PaymentFeeData(0.0, 0.0, 0.0, false));

        $this->assertFalse($this->validator()->validate(
            $this->apiPayment('43.20', 'EUR'),
            387.84,
            387.84,
            'EUR',
            $this->paymentMethod(),
            false
        ));
    }

    public function testPaymentHigherThanOrderAndCartIsInvalid(): void
    {
        $this->paymentFeeProvider->method('getPaymentFee')->willReturn(new PaymentFeeData(0.0, 0.0, 0.0, false));

        $this->assertFalse($this->validator()->validate(
            $this->apiPayment('120.00', 'EUR'),
            100.0,
            100.0,
            'EUR',
            $this->paymentMethod(),
            false
        ));
    }

    public function testPaymentInDifferentCurrencyIsInvalid(): void
    {
        $this->paymentFeeProvider->method('getPaymentFee')->willReturn(new PaymentFeeData(0.0, 0.0, 0.0, false));

        $this->assertFalse($this->validator()->validate(
            $this->apiPayment('100.00', 'USD'),
            100.0,
            100.0,
            'EUR',
            $this->paymentMethod(),
            false
        ));
    }

    public function testExpressPaymentIgnoresMethodFee(): void
    {
        $this->paymentFeeProvider->expects($this->never())->method('getPaymentFee');

        $this->assertTrue($this->validator()->validate(
            $this->apiPayment('100.00', 'EUR'),
            102.42,
            100.0,
            'EUR',
            $this->paymentMethod(),
            true
        ));
    }

    private function validator(): OrderPaymentAmountValidator
    {
        return new OrderPaymentAmountValidator(new PaymentAmountValidator(), $this->paymentFeeProvider);
    }

    private function paymentMethod(): MolPaymentMethod
    {
        return $this->getMockBuilder(MolPaymentMethod::class)->disableOriginalConstructor()->getMock();
    }

    private function apiPayment(string $value, string $currency): \stdClass
    {
        $payment = new \stdClass();
        $payment->amount = new \stdClass();
        $payment->amount->value = $value;
        $payment->amount->currency = $currency;

        return $payment;
    }
}
