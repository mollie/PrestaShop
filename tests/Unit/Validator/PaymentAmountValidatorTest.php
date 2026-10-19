<?php

namespace Mollie\Tests\Unit\Validator;

use Mollie\DTO\PaymentFeeData;
use Mollie\Validator\PaymentAmountValidator;
use PHPUnit\Framework\TestCase;

class PaymentAmountValidatorTest extends TestCase
{
    /** @dataProvider amountProvider */
    public function testValidate(
        float $cartTotal,
        string $cartCurrency,
        PaymentFeeData $paymentFeeData,
        string $paidValue,
        string $paidCurrency,
        bool $expected
    ): void {
        $validator = new PaymentAmountValidator();

        $result = $validator->validate(
            $cartTotal,
            $cartCurrency,
            $paymentFeeData,
            $this->apiPayment($paidValue, $paidCurrency)
        );

        $this->assertSame($expected, $result);
    }

    public function amountProvider(): array
    {
        $noFee = new PaymentFeeData(0.0, 0.0, 0.0, false);
        $fee = new PaymentFeeData(2.42, 2.0, 21.0, true);

        return [
            'equal amounts without fee' => [108.0, 'EUR', $noFee, '108.00', 'EUR', true],
            'cart total higher than the payment' => [531.28, 'EUR', $noFee, '357.28', 'EUR', false],
            'cart total lower than the payment' => [10.0, 'EUR', $noFee, '108.00', 'EUR', false],
            'one cent difference' => [108.0, 'EUR', $noFee, '107.99', 'EUR', false],
            'float noise below a cent' => [108.00000000001, 'EUR', $noFee, '108.00', 'EUR', true],
            'different currency, same number' => [108.0, 'EUR', $noFee, '108.00', 'USD', false],
            'currency compare ignores case' => [108.0, 'eur', $noFee, '108.00', 'EUR', true],
            'fee active and included' => [100.0, 'EUR', $fee, '102.42', 'EUR', true],
            'fee active and missing' => [100.0, 'EUR', $fee, '100.00', 'EUR', false],
        ];
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
