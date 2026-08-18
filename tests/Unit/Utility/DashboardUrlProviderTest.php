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

namespace Mollie\Tests\Unit\Utility;

use Mollie\Utility\DashboardUrlProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mollie\Utility\DashboardUrlProvider
 */
class DashboardUrlProviderTest extends TestCase
{
    /**
     * @dataProvider provideTransactionIds
     */
    public function testItBuildsTheDashboardUrlForATransaction($transactionId, $expected): void
    {
        $result = DashboardUrlProvider::getTransactionDashboardUrl($transactionId);

        $this->assertSame($expected, $result);
    }

    public function provideTransactionIds(): array
    {
        return [
            'payment transaction' => [
                'tr_7UhSN1zuXS',
                'https://my.mollie.com/dashboard/payments/tr_7UhSN1zuXS',
            ],
            'legacy payment id' => [
                'pay_1234567890',
                'https://my.mollie.com/dashboard/payments/pay_1234567890',
            ],
            'order transaction' => [
                'ord_kEn1PlbGa',
                'https://my.mollie.com/dashboard/orders/ord_kEn1PlbGa',
            ],
            'empty string returns null' => ['', null],
            'null returns null' => [null, null],
        ];
    }
}
