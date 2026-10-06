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

namespace Mollie\Tests\Integration\Repository;

use Db;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Tests\Integration\BaseTestCase;

/**
 * PIPRES-850: the return page trusts the transaction reference in the request. It must only
 * ever hand back a payment that belongs to the cart the shopper is checked against, so a
 * reference from someone else's cart cannot be used to read that payment from Mollie.
 */
class PaymentMethodRepositoryCartBindingTest extends BaseTestCase
{
    const OWNED_TRANSACTION = 'tr_pipres850_owned';
    const OTHER_TRANSACTION = 'tr_pipres850_other';
    const OWNED_CART = 850001;
    const OTHER_CART = 850002;

    /** @var PaymentMethodRepositoryInterface */
    private $paymentMethodRepository;

    protected function setUp()
    {
        parent::setUp();

        $this->paymentMethodRepository = $this->getService(PaymentMethodRepositoryInterface::class);
    }

    public function testItReturnsThePaymentWhenItBelongsToTheCart()
    {
        $this->insertPayment(self::OWNED_TRANSACTION, self::OWNED_CART);

        $payment = $this->paymentMethodRepository->getPaymentByTransactionIdForCart(self::OWNED_TRANSACTION, self::OWNED_CART);

        $this->assertIsArray($payment);
        $this->assertSame(self::OWNED_TRANSACTION, $payment['transaction_id']);
        $this->assertSame(self::OWNED_CART, (int) $payment['cart_id']);
    }

    public function testItRejectsATransactionFromAnotherCart()
    {
        $this->insertPayment(self::OTHER_TRANSACTION, self::OTHER_CART);

        $payment = $this->paymentMethodRepository->getPaymentByTransactionIdForCart(self::OTHER_TRANSACTION, self::OWNED_CART);

        $this->assertFalse($payment);
    }

    public function testItReturnsFalseForAnUnknownTransaction()
    {
        $payment = $this->paymentMethodRepository->getPaymentByTransactionIdForCart('tr_pipres850_missing', self::OWNED_CART);

        $this->assertFalse($payment);
    }

    /**
     * The cart_id column is nullable. A row with a null cart belongs to no cart, so it must
     * never resolve for a shopper's cart. The controller relies on this to fail closed.
     */
    public function testItRejectsATransactionWithNoCart()
    {
        $this->insertPaymentWithNullCart(self::OWNED_TRANSACTION);

        $payment = $this->paymentMethodRepository->getPaymentByTransactionIdForCart(self::OWNED_TRANSACTION, self::OWNED_CART);

        $this->assertFalse($payment);
    }

    private function insertPayment($transactionId, $cartId)
    {
        Db::getInstance()->delete('mollie_payments', '`transaction_id` = \'' . pSQL($transactionId) . '\'');

        Db::getInstance()->insert('mollie_payments', [
            'transaction_id' => pSQL($transactionId),
            'cart_id' => (int) $cartId,
            'order_id' => 0,
            'order_reference' => 'mol_pipres850',
            'method' => 'creditcard',
            'bank_status' => pSQL(PaymentStatus::STATUS_OPEN),
            'reason' => '',
            'created_at' => ['type' => 'sql', 'value' => 'NOW()'],
        ]);
    }

    private function insertPaymentWithNullCart($transactionId)
    {
        Db::getInstance()->delete('mollie_payments', '`transaction_id` = \'' . pSQL($transactionId) . '\'');

        Db::getInstance()->execute(
            'INSERT INTO `' . _DB_PREFIX_ . 'mollie_payments` (`transaction_id`, `cart_id`, `order_id`, `order_reference`, `method`, `bank_status`, `reason`, `created_at`)'
            . ' VALUES (\'' . pSQL($transactionId) . '\', NULL, 0, \'mol_pipres850\', \'creditcard\', \'' . pSQL(PaymentStatus::STATUS_OPEN) . '\', \'\', NOW())'
        );
    }
}
