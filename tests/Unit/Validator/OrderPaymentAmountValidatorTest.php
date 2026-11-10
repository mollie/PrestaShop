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

    /** @dataProvider statusProvider */
    public function testAppliesToStatus(string $status, bool $expected): void
    {
        $this->assertSame($expected, $this->validator()->appliesToStatus($status));
    }

    public function statusProvider(): array
    {
        return [
            'paid' => ['paid', true],
            'authorized' => ['authorized', true],
            'order shipping' => ['shipping', true],
            'order completed' => ['completed', true],
            'open' => ['open', false],
            'pending' => ['pending', false],
            'expired' => ['expired', false],
            'canceled' => ['canceled', false],
            'failed' => ['failed', false],
            'order created' => ['created', false],
        ];
    }

    /** @dataProvider orderTotalProvider */
    public function testMatchesOrderTotal(float $orderTotal, string $orderCurrency, string $paidValue, string $paidCurrency, bool $expected): void
    {
        $this->assertSame($expected, $this->validator()->matchesOrderTotal(
            $this->apiPayment($paidValue, $paidCurrency),
            $orderTotal,
            $orderCurrency
        ));
    }

    public function orderTotalProvider(): array
    {
        return [
            'equal amount' => [387.84, 'EUR', '387.84', 'EUR', true],
            'lower amount' => [387.84, 'EUR', '43.20', 'EUR', false],
            'higher amount' => [100.0, 'EUR', '120.00', 'EUR', false],
            'different currency' => [100.0, 'EUR', '100.00', 'USD', false],
        ];
    }

    public function testMatchesOrderTotalDoesNotLookUpFee(): void
    {
        $this->paymentFeeProvider->expects($this->never())->method('getPaymentFee');

        $this->validator()->matchesOrderTotal($this->apiPayment('100.00', 'EUR'), 100.0, 'EUR');
    }

    /** @dataProvider cartTotalProvider */
    public function testMatchesCartTotal(PaymentFeeData $fee, float $cartTotal, string $paidValue, string $paidCurrency, bool $expected): void
    {
        $this->paymentFeeProvider->method('getPaymentFee')->willReturn($fee);

        $this->assertSame($expected, $this->validator()->matchesCartTotal(
            $this->apiPayment($paidValue, $paidCurrency),
            $cartTotal,
            'EUR',
            $this->paymentMethod(),
            false
        ));
    }

    public function cartTotalProvider(): array
    {
        $noFee = new PaymentFeeData(0.0, 0.0, 0.0, false);
        $fee = new PaymentFeeData(2.42, 2.0, 21.0, true);

        return [
            'fee included' => [$fee, 100.0, '102.42', 'EUR', true],
            'fee missing' => [$fee, 100.0, '100.00', 'EUR', false],
            'lower amount' => [$noFee, 387.84, '43.20', 'EUR', false],
            'different currency' => [$noFee, 100.0, '100.00', 'USD', false],
        ];
    }

    public function testExpressPaymentIgnoresMethodFee(): void
    {
        $this->paymentFeeProvider->expects($this->never())->method('getPaymentFee');

        $this->assertTrue($this->validator()->matchesCartTotal(
            $this->apiPayment('100.00', 'EUR'),
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
