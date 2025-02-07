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

use Mollie\Api\Types\OrderStatus;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Config\Config;
use Mollie\Utility\MollieStatusUtility;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mollie\Utility\MollieStatusUtility
 */
class MollieStatusUtilityTest extends TestCase
{
    /**
     * @dataProvider providePaymentStatuses
     */
    public function testItCorrectlyIdentifiesFinishedPaymentStatuses(string $status, bool $expected): void
    {
        $result = MollieStatusUtility::isPaymentFinished($status);

        $this->assertSame($expected, $result, sprintf('Status "%s" should return %s.', $status, $expected ? 'true' : 'false'));
    }

    public function providePaymentStatuses(): array
    {
        return [
            'completed_order_status' => [OrderStatus::STATUS_COMPLETED, true],
            'paid_order_status' => [OrderStatus::STATUS_PAID, true],
            'shipping_order_status' => [OrderStatus::STATUS_SHIPPING, true],
            'authorized_payment_status' => [PaymentStatus::STATUS_AUTHORIZED, true],
            'paid_payment_status' => [PaymentStatus::STATUS_PAID, true],
            'paid_on_backorder_status' => [Config::STATUS_PAID_ON_BACKORDER, true],
            'pending_order_status' => [OrderStatus::STATUS_PENDING, false],
            'failed_payment_status' => [PaymentStatus::STATUS_FAILED, false],
            'cancelled_order_status' => [OrderStatus::STATUS_CANCELED, false],
            'unknown_status' => ['unknown_status', false],
        ];
    }
}
