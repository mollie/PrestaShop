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

use Mollie\Api\Types\PaymentStatus;
use Mollie\Utility\PaymentOverviewUtility;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mollie\Utility\PaymentOverviewUtility
 */
class PaymentOverviewUtilityTest extends TestCase
{
    /**
     * @dataProvider provideAttempts
     */
    public function testItSelectsTheAttemptsWorthShowing(string $status, int $orderId, int $ageHours, bool $expected): void
    {
        $result = PaymentOverviewUtility::isOverviewAttempt($status, $orderId, $ageHours, 24);

        $this->assertSame($expected, $result);
    }

    public function provideAttempts(): array
    {
        return [
            'failed is always shown' => [PaymentStatus::STATUS_FAILED, 0, 0, true],
            'canceled is always shown' => [PaymentStatus::STATUS_CANCELED, 0, 0, true],
            'expired is always shown' => [PaymentStatus::STATUS_EXPIRED, 0, 0, true],
            'failed with an order is still shown' => [PaymentStatus::STATUS_FAILED, 44, 500, true],

            // Bank transfer sits at open for up to MOLLIE_BANKTRANSFER_DUE_DAYS but always has an
            // order behind it, so the order id is what keeps a legitimate wait off the list.
            'open bank transfer with an order is never shown' => [PaymentStatus::STATUS_OPEN, 44, 24 * 14, false],
            'pending with an order is never shown' => [PaymentStatus::STATUS_PENDING, 44, 24 * 14, false],

            'open inside the grace period is not shown' => [PaymentStatus::STATUS_OPEN, 0, 1, false],
            'open exactly at the grace period is not shown' => [PaymentStatus::STATUS_OPEN, 0, 24, false],
            'open past the grace period is shown' => [PaymentStatus::STATUS_OPEN, 0, 25, true],
            'pending past the grace period is shown' => [PaymentStatus::STATUS_PENDING, 0, 25, true],

            'paid is never shown' => [PaymentStatus::STATUS_PAID, 0, 5000, false],
            'authorized is never shown' => [PaymentStatus::STATUS_AUTHORIZED, 0, 5000, false],
            'an unknown status is never shown' => ['whatever', 0, 5000, false],
            'an empty status is never shown' => ['', 0, 5000, false],
        ];
    }

    public function testTheWhereClauseCarriesEveryConditionTheRuleDependsOn(): void
    {
        $clause = PaymentOverviewUtility::buildOverviewWhereClause('a', 24);

        foreach (PaymentOverviewUtility::TERMINAL_STATUSES as $status) {
            $this->assertContains("'" . $status . "'", $clause);
        }

        foreach (PaymentOverviewUtility::STUCK_STATUSES as $status) {
            $this->assertContains("'" . $status . "'", $clause);
        }

        $this->assertContains('`a`.`order_id` = 0', $clause);
        $this->assertContains('INTERVAL 24 HOUR', $clause);
        $this->assertContains('`a`.`created_at` <', $clause);
    }

    public function testItWritesTheClauseAgainstTheGivenAlias(): void
    {
        $clause = PaymentOverviewUtility::buildOverviewWhereClause('mp', 24);

        $this->assertContains('`mp`.`bank_status`', $clause);
        $this->assertNotContains('`a`.', $clause);
    }

    public function testItStripsBackticksOutOfTheAlias(): void
    {
        $clause = PaymentOverviewUtility::buildOverviewWhereClause('a` OR 1=1 -- ', 24);

        $this->assertNotContains(' OR 1=1', $clause);
    }

    public function testANegativeGracePeriodIsClampedToZero(): void
    {
        $clause = PaymentOverviewUtility::buildOverviewWhereClause('a', -5);

        $this->assertContains('INTERVAL 0 HOUR', $clause);
    }
}
