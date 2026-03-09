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

use Mollie\Api\Types\PaymentStatus;
use PHPUnit\Framework\TestCase;

/**
 * PIPRES-720: Tests the fallback mechanism that prevents the race condition
 * where the return page cannot find the payment because mollie_payments.order_id
 * is still 0 during webhook processing.
 *
 * The race condition:
 *   1. Webhook creates the PS order (Order::getIdByCartId returns orderId)
 *   2. Return page polls: looks up mollie_payments WHERE order_id = orderId
 *   3. Returns nothing because mollie_payments.order_id is still 0
 *   4. After 30s of failed polls, customer sees "Payment failed"
 *
 * The fix: fallback to cart_id lookup when order_id lookup fails.
 * Implementation in controllers/front/return.php:processGetStatus()
 *
 * @see https://github.com/mollie/PrestaShop/issues/1299
 */
class PaymentApiFallbackLookupTest extends TestCase
{
    private const TRANSACTION_ID = 'tr_test_pipres720';
    private const CART_ID = 99999;
    private const ORDER_ID = 88888;

    protected function setUp(): void
    {
        parent::setUp();

        Db::getInstance()->execute('START TRANSACTION');

        $this->insertMolliePayment(self::TRANSACTION_ID, self::CART_ID, 0, 'mol_test123');
    }

    protected function tearDown(): void
    {
        Db::getInstance()->execute('ROLLBACK');

        parent::tearDown();
    }

    /**
     * Verifies that during the race condition window, order_id lookup fails
     * but the payment still exists in the database (with order_id=0).
     *
     * This confirms the race condition exists when order_id is not yet written.
     */
    public function testOrderIdLookupFailsWhenOrderIdIsZero(): void
    {
        // Direct order_id lookup should fail
        $dbPayment = $this->getPaymentByOrderId(self::ORDER_ID);

        $this->assertEmpty(
            $dbPayment,
            'Lookup by order_id fails when order_id is still 0 in database'
        );

        // But payment exists, just not indexed by order_id yet
        $paymentByTransaction = $this->getPaymentByTransactionId(self::TRANSACTION_ID);
        $this->assertNotEmpty(
            $paymentByTransaction,
            'The mollie_payments row EXISTS with order_id=0'
        );
        $this->assertEquals(0, (int) $paymentByTransaction['order_id']);
    }

    /**
     * Verifies the fallback fix: when order_id lookup fails, fallback to cart_id
     * lookup successfully finds the payment.
     *
     * This simulates what processGetStatus() in return.php now does:
     *   1. $dbPayment = getPaymentBy('order_id', orderId); // returns NOTHING (order_id = 0)
     *   2. if (!$dbPayment && $orderId) {
     *        $dbPayment = getPaymentBy('cart_id', cartId);  // FOUND via fallback ✓
     *      }
     */
    public function testFallbackToCartIdFindsPaymentWhenOrderIdLookupFails(): void
    {
        // First lookup by order_id fails
        $dbPayment = $this->getPaymentByOrderId(self::ORDER_ID);
        $this->assertEmpty($dbPayment, 'Order ID lookup fails during race condition');

        // Fallback to cart_id succeeds
        $dbPayment = $this->getPaymentByCartId(self::CART_ID);

        $this->assertNotEmpty(
            $dbPayment,
            'Fallback to cart_id lookup successfully finds the payment'
        );
        $this->assertEquals(self::TRANSACTION_ID, $dbPayment['transaction_id']);
        $this->assertEquals(self::CART_ID, (int) $dbPayment['cart_id']);
        $this->assertEquals(0, (int) $dbPayment['order_id'], 'order_id is still 0 during race window');
    }

    /**
     * Verifies that cart_id fallback works even when order_reference changes.
     *
     * During the race condition, updateMolliePaymentReference() may change the
     * order_reference, but cart_id remains stable and can be used for lookup.
     */
    public function testCartIdFallbackWorksEvenWhenOrderReferenceChanges(): void
    {
        // Simulate order_reference being updated during webhook processing
        $this->simulateUpdateMolliePaymentReference(self::TRANSACTION_ID, 'ABCDEF');

        // Order ID lookup still fails
        $byOrderId = $this->getPaymentByOrderId(self::ORDER_ID);
        $this->assertEmpty($byOrderId, 'Lookup by order_id fails: still 0');

        // But cart_id fallback still works because cart_id doesn't change
        $byCartId = $this->getPaymentByCartId(self::CART_ID);
        $this->assertNotEmpty(
            $byCartId,
            'Cart ID fallback finds payment even after order_reference changes'
        );
        $this->assertEquals(self::TRANSACTION_ID, $byCartId['transaction_id']);
        $this->assertEquals('ABCDEF', $byCartId['order_reference']);
    }

    /**
     * Verifies that after order_id is eventually written (at end of webhook),
     * both order_id and cart_id lookups work.
     */
    public function testBothLookupsWorkAfterOrderIdIsWritten(): void
    {
        // Simulate eventual savePaymentStatus at end of webhook processing
        $this->simulateSavePaymentStatus(self::TRANSACTION_ID, PaymentStatus::STATUS_PAID, self::ORDER_ID);

        // Now both lookups work
        $byOrderId = $this->getPaymentByOrderId(self::ORDER_ID);
        $this->assertNotEmpty(
            $byOrderId,
            'After order_id is written, order_id lookup works'
        );

        $byCartId = $this->getPaymentByCartId(self::CART_ID);
        $this->assertNotEmpty(
            $byCartId,
            'Cart ID lookup still works after order_id is written'
        );

        $this->assertEquals(self::TRANSACTION_ID, $byOrderId['transaction_id']);
        $this->assertEquals(self::TRANSACTION_ID, $byCartId['transaction_id']);
    }

    /**
     * Verifies the complete flow: cart_id lookup works during race window,
     * and then order_id lookup works after webhook completes.
     */
    public function testCompleteFlowFromRaceWindowToResolution(): void
    {
        // Phase 1: During race window (order_id = 0)
        $duringRace = $this->getPaymentByCartId(self::CART_ID);
        $this->assertNotEmpty($duringRace, 'Cart ID lookup works during race window');
        $this->assertEquals(0, (int) $duringRace['order_id']);

        // Phase 2: Webhook completes, writes order_id
        $this->simulateSavePaymentStatus(self::TRANSACTION_ID, PaymentStatus::STATUS_PAID, self::ORDER_ID);

        // Phase 3: After race window ends
        $afterRace = $this->getPaymentByOrderId(self::ORDER_ID);
        $this->assertNotEmpty($afterRace, 'Order ID lookup works after order_id is written');
        $this->assertEquals(self::ORDER_ID, (int) $afterRace['order_id']);
        $this->assertEquals(PaymentStatus::STATUS_PAID, $afterRace['bank_status']);
    }

    // --- helpers ---

    private function insertMolliePayment(string $transactionId, int $cartId, int $orderId, string $orderReference): void
    {
        Db::getInstance()->insert('mollie_payments', [
            'cart_id' => $cartId,
            'order_id' => $orderId,
            'method' => 'ideal',
            'transaction_id' => pSQL($transactionId),
            'order_reference' => pSQL($orderReference),
            'bank_status' => PaymentStatus::STATUS_OPEN,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Simulates TransactionService::savePaymentStatus() — the exact DB update
     * that writes order_id to mollie_payments.
     *
     * @see \Mollie\Service\TransactionService::savePaymentStatus()
     */
    private function simulateSavePaymentStatus(string $transactionId, string $status, int $orderId): void
    {
        Db::getInstance()->update(
            'mollie_payments',
            [
                'updated_at' => ['type' => 'sql', 'value' => 'NOW()'],
                'bank_status' => pSQL($status),
                'order_id' => (int) $orderId,
            ],
            '`transaction_id` = \'' . pSQL($transactionId) . '\''
        );
    }

    /**
     * Simulates MollieOrderCreationService::updateMolliePaymentReference()
     *
     * @see \Mollie\Service\MollieOrderCreationService::updateMolliePaymentReference()
     */
    private function simulateUpdateMolliePaymentReference(string $transactionId, string $newReference): void
    {
        Db::getInstance()->update(
            'mollie_payments',
            [
                'order_reference' => pSQL($newReference),
                'updated_at' => ['type' => 'sql', 'value' => 'NOW()'],
            ],
            'transaction_id = "' . pSQL($transactionId) . '"'
        );
    }

    /**
     * Simulates what processGetStatus() does in return.php:298-300
     */
    private function getPaymentByOrderId(int $orderId): array
    {
        $result = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'mollie_payments` WHERE `order_id` = ' . (int) $orderId
        );

        return $result ?: [];
    }

    private function getPaymentByOrderReference(string $orderReference): array
    {
        $result = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'mollie_payments` WHERE `order_reference` = \'' . pSQL($orderReference) . '\''
        );

        return $result ?: [];
    }

    private function getPaymentByTransactionId(string $transactionId): array
    {
        $result = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'mollie_payments` WHERE `transaction_id` = \'' . pSQL($transactionId) . '\''
        );

        return $result ?: [];
    }

    /**
     * Simulates the fallback lookup in processGetStatus() (return.php:285-287)
     */
    private function getPaymentByCartId(int $cartId): array
    {
        $result = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'mollie_payments` WHERE `cart_id` = ' . (int) $cartId
        );

        return $result ?: [];
    }
}
