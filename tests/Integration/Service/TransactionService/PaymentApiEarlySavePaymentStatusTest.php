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
 * PIPRES-720: Reproduces the race condition where the return page cannot find the payment
 * because mollie_payments.order_id is still 0 during webhook processing.
 *
 * The race condition:
 *   1. Webhook creates the PS order (Order::getIdByCartId returns orderId)
 *   2. Return page polls: looks up mollie_payments WHERE order_id = orderId
 *   3. Returns nothing because mollie_payments.order_id is still 0
 *   4. After 30s of failed polls, customer sees "Payment failed"
 *
 * The fix: write order_id to mollie_payments immediately after createOrder(),
 * not at the very end of webhook processing.
 *
 * @see https://github.com/mollie/PrestaShop/issues/1299
 */
class PaymentApiEarlySavePaymentStatusTest extends TestCase
{
    private const TRANSACTION_ID = 'tr_test_pipres720';
    private const CART_ID = 99999;
    private const ORDER_ID = 88888;

    protected function setUp(): void
    {
        parent::setUp();

        Db::getInstance()->execute('START TRANSACTION');

        $this->insertMolliePayment(self::TRANSACTION_ID, self::CART_ID, 0, 'mol_test123');

        // Verify the insert worked before continuing
        $inserted = $this->getPaymentByTransactionId(self::TRANSACTION_ID);
        if (empty($inserted)) {
            throw new \RuntimeException('Test setup failed: could not insert initial payment record. ' . 'Transaction ID: ' . self::TRANSACTION_ID . ', ' . 'DB Error: ' . Db::getInstance()->getMsgError());
        }
    }

    protected function tearDown(): void
    {
        Db::getInstance()->execute('ROLLBACK');

        parent::tearDown();
    }

    /**
     * Reproduces the race condition: return page polls for order_id but finds nothing
     * because order_id is still 0 in mollie_payments.
     *
     * This is exactly what processGetStatus() in return.php does:
     *   $orderId = Order::getIdByCartId($cartId);     // returns 88888 (order exists)
     *   $dbPayment = getPaymentBy('order_id', 88888); // returns NOTHING (order_id = 0)
     *   if (!$dbPayment) { exit({success: false}); }  // customer sees failure
     */
    public function testReturnPageCannotFindPaymentWhenOrderIdIsZero(): void
    {
        $dbPayment = $this->getPaymentByOrderId(self::ORDER_ID);

        $this->assertEmpty(
            $dbPayment,
            'This reproduces the bug: mollie_payments has order_id=0, so lookup by order_id finds nothing. '
            . 'The return page cannot find the payment even though the PS order exists.'
        );

        $paymentByTransaction = $this->getPaymentByTransactionId(self::TRANSACTION_ID);
        $this->assertNotEmpty(
            $paymentByTransaction,
            'The mollie_payments row EXISTS — it just has order_id=0, making it invisible to the return page'
        );
        $this->assertEquals(0, (int) $paymentByTransaction['order_id']);
    }

    /**
     * Verifies the fix: after savePaymentStatus writes order_id, the return page
     * can immediately find the payment.
     *
     * This simulates what happens after the fix adds savePaymentStatus() right after createOrder():
     *   1. createOrder() returns orderId = 88888
     *   2. savePaymentStatus() writes order_id = 88888 to mollie_payments
     *   3. Return page polls: getPaymentBy('order_id', 88888) → FOUND ✓
     */
    public function testReturnPageFindsPaymentAfterEarlySavePaymentStatus(): void
    {
        $this->simulateSavePaymentStatus(self::TRANSACTION_ID, PaymentStatus::STATUS_PAID, self::ORDER_ID);

        $dbPayment = $this->getPaymentByOrderId(self::ORDER_ID);

        $this->assertNotEmpty(
            $dbPayment,
            'After savePaymentStatus writes order_id, the return page should find the payment immediately'
        );
        $this->assertEquals(self::TRANSACTION_ID, $dbPayment['transaction_id']);
        $this->assertEquals(self::ORDER_ID, (int) $dbPayment['order_id']);
    }

    /**
     * Reproduces the double-lookup failure: during webhook processing,
     * updateMolliePaymentReference() changes order_reference from mol_XXX to PS reference.
     * This breaks the order_reference lookup while order_id is still 0.
     *
     * Return page has two ways to find the payment:
     *   1. getPaymentBy('order_id', 88888) → fails (order_id = 0)
     *   2. getPaymentBy('order_reference', 'mol_test123') → fails (changed to 'ABCDEF')
     *
     * Both doors are locked at the same time.
     */
    public function testBothLookupMethodsFailDuringRaceWindow(): void
    {
        $this->simulateUpdateMolliePaymentReference(self::TRANSACTION_ID, 'ABCDEF');

        $byOrderId = $this->getPaymentByOrderId(self::ORDER_ID);
        $this->assertEmpty($byOrderId, 'Lookup by order_id fails: still 0');

        $byOldReference = $this->getPaymentByOrderReference('mol_test123');
        $this->assertEmpty($byOldReference, 'Lookup by original order_reference fails: already changed');

        $byNewReference = $this->getPaymentByOrderReference('ABCDEF');
        $this->assertNotEmpty($byNewReference, 'The row exists under the new reference, but return page uses the old one from the URL');
    }

    /**
     * Verifies fix eliminates the double-lookup failure: if savePaymentStatus()
     * runs BEFORE updateMolliePaymentReference(), order_id is already set.
     * Even after reference changes, the order_id lookup still works.
     */
    public function testEarlySavePaymentStatusPreventsDoubleLookupFailure(): void
    {
        $this->simulateSavePaymentStatus(self::TRANSACTION_ID, PaymentStatus::STATUS_PAID, self::ORDER_ID);

        $this->simulateUpdateMolliePaymentReference(self::TRANSACTION_ID, 'ABCDEF');

        $byOrderId = $this->getPaymentByOrderId(self::ORDER_ID);
        $this->assertNotEmpty(
            $byOrderId,
            'With the fix, order_id lookup works even after order_reference changes'
        );
        $this->assertEquals(self::TRANSACTION_ID, $byOrderId['transaction_id']);
    }

    /**
     * Verifies that savePaymentStatus is idempotent — calling it twice with the same
     * values produces the same result. This is important because the fix adds an early
     * call, and the existing call at the end of processTransaction still runs.
     */
    public function testSavePaymentStatusIsIdempotent(): void
    {
        $this->simulateSavePaymentStatus(self::TRANSACTION_ID, PaymentStatus::STATUS_PAID, self::ORDER_ID);

        $afterFirstCall = $this->getPaymentByTransactionId(self::TRANSACTION_ID);
        $this->assertNotEmpty($afterFirstCall, 'First call should create/update payment record');

        $this->simulateSavePaymentStatus(self::TRANSACTION_ID, PaymentStatus::STATUS_PAID, self::ORDER_ID);

        $afterSecondCall = $this->getPaymentByTransactionId(self::TRANSACTION_ID);
        $this->assertNotEmpty($afterSecondCall, 'Second call should return same payment record');

        $this->assertEquals((int) $afterFirstCall['order_id'], (int) $afterSecondCall['order_id']);
        $this->assertEquals($afterFirstCall['bank_status'], $afterSecondCall['bank_status']);
    }

    // --- helpers ---

    private function insertMolliePayment(string $transactionId, int $cartId, int $orderId, string $orderReference): void
    {
        $result = Db::getInstance()->insert('mollie_payments', [
            'cart_id' => $cartId,
            'order_id' => $orderId,
            'method' => 'ideal',
            'transaction_id' => pSQL($transactionId),
            'order_reference' => pSQL($orderReference),
            'bank_status' => PaymentStatus::STATUS_OPEN,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$result) {
            throw new \RuntimeException('Failed to insert test payment data. DB Error: ' . Db::getInstance()->getMsgError());
        }
    }

    /**
     * Simulates TransactionService::savePaymentStatus() — the exact DB update
     * that writes order_id to mollie_payments.
     *
     * @see \Mollie\Service\TransactionService::savePaymentStatus()
     */
    private function simulateSavePaymentStatus(string $transactionId, string $status, int $orderId): void
    {
        $result = Db::getInstance()->update(
            'mollie_payments',
            [
                'updated_at' => ['type' => 'sql', 'value' => 'NOW()'],
                'bank_status' => pSQL($status),
                'order_id' => (int) $orderId,
            ],
            '`transaction_id` = \'' . pSQL($transactionId) . '\''
        );

        if (!$result) {
            throw new \RuntimeException('Failed to update payment status. DB Error: ' . Db::getInstance()->getMsgError());
        }
    }

    /**
     * Simulates MollieOrderCreationService::updateMolliePaymentReference()
     *
     * @see \Mollie\Service\MollieOrderCreationService::updateMolliePaymentReference()
     */
    private function simulateUpdateMolliePaymentReference(string $transactionId, string $newReference): void
    {
        $result = Db::getInstance()->update(
            'mollie_payments',
            [
                'order_reference' => pSQL($newReference),
                'updated_at' => ['type' => 'sql', 'value' => 'NOW()'],
            ],
            'transaction_id = "' . pSQL($transactionId) . '"'
        );

        if (!$result) {
            throw new \RuntimeException('Failed to update payment reference. DB Error: ' . Db::getInstance()->getMsgError());
        }
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
}
