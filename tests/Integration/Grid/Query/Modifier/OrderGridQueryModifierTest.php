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

namespace Mollie\Tests\Integration\Grid\Query\Modifier;

use Db;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\QueryBuilder;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Grid\Query\Modifier\OrderGridQueryModifier;
use PHPUnit\Framework\TestCase;

/**
 * PIPRES-890: the order grid must get exactly one transaction id per order without joining
 * mollie_payments, because the join made MariaDB sort every order before applying the page limit.
 *
 * Plain TestCase on purpose: the integration BaseTestCase truncates customers and addresses.
 */
class OrderGridQueryModifierTest extends TestCase
{
    const CART_OFFSET = 890000;

    const NO_PAYMENT = 1;
    const SINGLE_PAYMENT = 2;
    const UNLINKED_ATTEMPT_ONLY = 3;
    const EXPIRED_THEN_PAID = 4;
    const PAID_THEN_OPEN = 5;
    const TWO_PAID = 6;
    const AUTHORIZED_THEN_OPEN = 7;
    const SAME_TIME_UNPAID = 8;
    const UNLINKED_NEWER_THAN_PAID = 9;

    const ORDER_COUNT = 9;

    protected function setUp()
    {
        parent::setUp();

        $this->deletePayments();

        $payments = [
            ['tr_890_single', self::SINGLE_PAYMENT, true, PaymentStatus::STATUS_PAID, 10],
            ['tr_890_unlinked', self::UNLINKED_ATTEMPT_ONLY, false, PaymentStatus::STATUS_CANCELED, 10],
            ['tr_890_expired', self::EXPIRED_THEN_PAID, true, PaymentStatus::STATUS_EXPIRED, 10],
            ['tr_890_paid_later', self::EXPIRED_THEN_PAID, true, PaymentStatus::STATUS_PAID, 11],
            ['tr_890_paid_first', self::PAID_THEN_OPEN, true, PaymentStatus::STATUS_PAID, 10],
            ['tr_890_open_later', self::PAID_THEN_OPEN, true, PaymentStatus::STATUS_OPEN, 11],
            ['tr_890_paid_old', self::TWO_PAID, true, PaymentStatus::STATUS_PAID, 10],
            ['tr_890_paid_new', self::TWO_PAID, true, PaymentStatus::STATUS_PAID, 11],
            ['tr_890_authorized', self::AUTHORIZED_THEN_OPEN, true, PaymentStatus::STATUS_AUTHORIZED, 10],
            ['tr_890_open_after_auth', self::AUTHORIZED_THEN_OPEN, true, PaymentStatus::STATUS_OPEN, 11],
            ['tr_890_tie_a', self::SAME_TIME_UNPAID, true, PaymentStatus::STATUS_OPEN, 10],
            ['tr_890_tie_b', self::SAME_TIME_UNPAID, true, PaymentStatus::STATUS_EXPIRED, 10],
            ['tr_890_linked_paid', self::UNLINKED_NEWER_THAN_PAID, true, PaymentStatus::STATUS_PAID, 10],
            ['tr_890_unlinked_newer', self::UNLINKED_NEWER_THAN_PAID, false, PaymentStatus::STATUS_PAID, 11],
        ];

        foreach ($payments as list($transactionId, $order, $linked, $status, $hour)) {
            Db::getInstance()->insert('mollie_payments', [
                'transaction_id' => $transactionId,
                'cart_id' => self::CART_OFFSET + $order,
                'order_id' => $linked ? $order : 0,
                'order_reference' => 'PIPRES890',
                'method' => 'ideal',
                'bank_status' => $status,
                'created_at' => sprintf('2026-01-01 %02d:00:00', $hour),
            ]);
        }
    }

    protected function tearDown()
    {
        $this->deletePayments();

        parent::tearDown();
    }

    public function testItReturnsOneRowPerOrderWithThePaymentTheRowActionsExpect()
    {
        $rows = Db::getInstance()->executeS($this->modifiedQueryBuilder()->getSQL() . ' ORDER BY o.id_order ASC');

        $this->assertSame([
            self::NO_PAYMENT => null,
            self::SINGLE_PAYMENT => 'tr_890_single',
            self::UNLINKED_ATTEMPT_ONLY => null,
            self::EXPIRED_THEN_PAID => 'tr_890_paid_later',
            self::PAID_THEN_OPEN => 'tr_890_paid_first',
            self::TWO_PAID => 'tr_890_paid_new',
            self::AUTHORIZED_THEN_OPEN => 'tr_890_authorized',
            self::SAME_TIME_UNPAID => 'tr_890_tie_b',
            self::UNLINKED_NEWER_THAN_PAID => 'tr_890_linked_paid',
        ], array_column($rows, 'transaction_id', 'id_order'));
        $this->assertCount(self::ORDER_COUNT, $rows);
    }

    public function testItDoesNotJoinThePaymentsTable()
    {
        $this->assertNotRegExp('/\bJOIN\b/i', $this->modifiedQueryBuilder()->getSQL());
    }

    private function modifiedQueryBuilder()
    {
        $orders = [];
        foreach (range(1, self::ORDER_COUNT) as $orderId) {
            $orders[] = sprintf('SELECT %d AS id_order, %d AS id_cart', $orderId, self::CART_OFFSET + $orderId);
        }

        $queryBuilder = (new QueryBuilder(DriverManager::getConnection(['driver' => 'pdo_mysql', 'dbname' => _DB_NAME_])))
            ->select('o.id_order')
            ->from('(' . implode(' UNION ALL ', $orders) . ')', 'o');

        (new OrderGridQueryModifier())->modify($queryBuilder);

        return $queryBuilder;
    }

    private function deletePayments()
    {
        Db::getInstance()->delete(
            'mollie_payments',
            '`cart_id` BETWEEN ' . (self::CART_OFFSET + 1) . ' AND ' . (self::CART_OFFSET + self::ORDER_COUNT)
        );
    }
}
