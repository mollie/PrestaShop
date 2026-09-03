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

namespace Mollie\Tests\Integration\Service\TransactionService;

use Db;
use Mollie\Api\Types\OrderStatus;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Config\Config;
use Mollie\Exception\TransactionException;
use Mollie\Service\TransactionService;
use Mollie\Tests\Integration\BaseTestCase;
use Mollie\Tests\Integration\Factory\CartFactory;
use Mollie\Utility\SecureKeyUtility;
use Order;

/**
 * PIPRES-786: a webhook for an attempt that never produced an order used to leave the
 * mollie_payments row at "open" forever. It must now record the terminal status
 * without creating an order and without touching order_id.
 */
class TransactionServiceFailedAttemptTest extends BaseTestCase
{
    const TRANSACTION_ID = 'tr_pipres786_service';
    const ORDER_TRANSACTION_ID = 'ord_pipres786_service';

    /** @var TransactionService */
    private $transactionService;

    /** @var \Cart */
    private $cart;

    protected function setUp()
    {
        parent::setUp();

        $this->transactionService = $this->getService(TransactionService::class);
        $this->cart = CartFactory::initialize()->create();
    }

    public function testItRecordsAFailedPaymentsApiAttempt()
    {
        $this->insertPayment(self::TRANSACTION_ID, 'creditcard');

        $this->transactionService->processTransaction($this->buildPayment());

        $payment = $this->getPayment(self::TRANSACTION_ID);

        $this->assertSame(PaymentStatus::STATUS_FAILED, $payment['bank_status']);
        $this->assertSame('invalid_card_number', $payment['reason']);
        $this->assertSame(0, (int) $payment['order_id']);
    }

    /**
     * The Orders API never reports "failed" at order level, so a declined card leaves
     * the order at "created" and the status has to come from the embedded payment.
     */
    public function testItRecordsAFailedOrdersApiAttemptFromTheEmbeddedPayment()
    {
        $this->insertPayment(self::ORDER_TRANSACTION_ID, 'creditcard');

        $this->transactionService->processTransaction($this->buildOrder());

        $payment = $this->getPayment(self::ORDER_TRANSACTION_ID);

        $this->assertSame(PaymentStatus::STATUS_FAILED, $payment['bank_status']);
        $this->assertSame('invalid_card_number', $payment['reason']);
        $this->assertSame(0, (int) $payment['order_id']);
    }

    /**
     * The whole point of the ticket is visibility, not a behaviour change: a failed
     * attempt still must not produce a PrestaShop order.
     */
    public function testItDoesNotCreateAnOrder()
    {
        $this->insertPayment(self::TRANSACTION_ID, 'creditcard');

        $this->transactionService->processTransaction($this->buildPayment());

        $this->assertEmpty(Order::getIdByCartId((int) $this->cart->id));
    }

    public function testItLeavesANonTerminalAttemptAlone()
    {
        $this->insertPayment(self::TRANSACTION_ID, 'creditcard');

        $this->transactionService->processTransaction(
            $this->buildPayment(PaymentStatus::STATUS_PENDING)
        );

        $payment = $this->getPayment(self::TRANSACTION_ID);

        $this->assertSame(PaymentStatus::STATUS_OPEN, $payment['bank_status']);
        $this->assertSame('', (string) $payment['reason']);
    }

    public function testItRejectsAWrongSecureKeyBeforeWritingAnything()
    {
        $this->insertPayment(self::TRANSACTION_ID, 'creditcard');

        $this->expectException(TransactionException::class);

        $apiPayment = $this->buildPayment();
        $apiPayment->metadata->secure_key = 'not-the-right-key';

        try {
            $this->transactionService->processTransaction($apiPayment);
        } finally {
            $this->assertSame(PaymentStatus::STATUS_OPEN, $this->getPayment(self::TRANSACTION_ID)['bank_status']);
        }
    }

    private function buildPayment($status = PaymentStatus::STATUS_FAILED)
    {
        return (object) [
            'resource' => Config::MOLLIE_API_STATUS_PAYMENT,
            'id' => self::TRANSACTION_ID,
            'status' => $status,
            'description' => 'mol_pipres786payments',
            'details' => (object) ['failureReason' => 'invalid_card_number'],
            'metadata' => (object) [
                'cart_id' => (int) $this->cart->id,
                'secure_key' => $this->secureKey(),
            ],
        ];
    }

    private function buildOrder()
    {
        return (object) [
            'resource' => Config::MOLLIE_API_STATUS_ORDER,
            'id' => self::ORDER_TRANSACTION_ID,
            'status' => OrderStatus::STATUS_CREATED,
            'orderNumber' => 'mol_pipres786orders',
            'metadata' => (object) [
                'cart_id' => (int) $this->cart->id,
                'secure_key' => $this->secureKey(),
            ],
            '_embedded' => (object) [
                'payments' => [
                    (object) [
                        'resource' => Config::MOLLIE_API_STATUS_PAYMENT,
                        'id' => 'tr_pipres786embedded',
                        'status' => PaymentStatus::STATUS_FAILED,
                        'details' => (object) ['failureReason' => 'invalid_card_number'],
                    ],
                ],
            ],
        ];
    }

    private function secureKey()
    {
        return SecureKeyUtility::generateReturnKey(
            $this->cart->id_customer,
            $this->cart->id,
            'mollie'
        );
    }

    private function insertPayment($transactionId, $method)
    {
        Db::getInstance()->delete('mollie_payments', '`transaction_id` = \'' . pSQL($transactionId) . '\'');

        Db::getInstance()->insert('mollie_payments', [
            'transaction_id' => pSQL($transactionId),
            'cart_id' => (int) $this->cart->id,
            'order_id' => 0,
            'order_reference' => 'mol_pipres786',
            'method' => pSQL($method),
            'bank_status' => PaymentStatus::STATUS_OPEN,
            'reason' => '',
            'created_at' => ['type' => 'sql', 'value' => 'NOW()'],
        ]);
    }

    private function getPayment($transactionId)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'mollie_payments` WHERE `transaction_id` = \''
            . pSQL($transactionId) . '\''
        );
    }
}
