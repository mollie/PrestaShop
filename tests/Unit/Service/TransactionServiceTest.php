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

namespace Mollie\Tests\Unit\Service;

use Mollie\Service\TransactionService;
use Order;
use PHPUnit\Framework\TestCase;

class TransactionServiceTest extends TestCase
{
    /**
     * @dataProvider conversionRateProvider
     *
     * @param float $orderConversionRate
     */
    public function testOrderPaymentTakesConversionRateFromOrder($orderConversionRate)
    {
        $order = new Order();
        $order->reference = 'ABCDEFGHI';
        $order->conversion_rate = $orderConversionRate;

        $orderPayment = TransactionService::buildOrderPayment([
            'amount' => '24.20',
            'paymentName' => 'Bank transfer',
            'transactionId' => 'tr_test841',
            'currency' => 'EUR',
        ], $order);

        $this->assertSame($orderConversionRate, $orderPayment->conversion_rate);
        $this->assertSame('ABCDEFGHI', $orderPayment->order_reference);
        $this->assertSame('24.20', $orderPayment->amount);
        $this->assertSame('Bank transfer', $orderPayment->payment_method);
        $this->assertSame('tr_test841', $orderPayment->transaction_id);
    }

    public function conversionRateProvider()
    {
        return [
            'default currency' => [1.0],
            'foreign currency' => [1.0864],
        ];
    }
}
