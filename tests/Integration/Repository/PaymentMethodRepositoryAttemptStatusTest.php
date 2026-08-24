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
use Mollie\Config\Config;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Tests\Integration\BaseTestCase;

/**
 * PIPRES-786: covers the three guarantees updateAttemptStatus() has to give, each of
 * which protects an existing behaviour rather than the new feature itself.
 */
class PaymentMethodRepositoryAttemptStatusTest extends BaseTestCase
{
    const TRANSACTION_ID = 'tr_pipres786_attempt';

    /** @var PaymentMethodRepositoryInterface */
    private $paymentMethodRepository;

    protected function setUp()
    {
        parent::setUp();

        $this->paymentMethodRepository = $this->getService(PaymentMethodRepositoryInterface::class);
    }

    public function testItPersistsTheStatusAndReason()
    {
        $this->insertPayment(PaymentStatus::STATUS_OPEN);

        $result = $this->paymentMethodRepository->updateAttemptStatus(
            self::TRANSACTION_ID,
            PaymentStatus::STATUS_FAILED,
            'invalid_card_number'
        );

        $this->assertTrue($result);

        $payment = $this->getPayment();

        $this->assertSame(PaymentStatus::STATUS_FAILED, $payment['bank_status']);
        $this->assertSame('invalid_card_number', $payment['reason']);
        $this->assertNotNull($payment['updated_at']);
    }

    /**
     * OrderGridQueryModifier joins on mol.order_id > 0, so zeroing a real order id
     * would silently drop the transaction id from the back office order grid.
     */
    public function testItNeverTouchesTheOrderId()
    {
        $this->insertPayment(PaymentStatus::STATUS_OPEN, '', 4242);

        $this->paymentMethodRepository->updateAttemptStatus(
            self::TRANSACTION_ID,
            PaymentStatus::STATUS_FAILED,
            'invalid_card_number'
        );

        $this->assertSame(4242, (int) $this->getPayment()['order_id']);
    }

    /**
     * return.php reads the wrong amount reason back out, so an empty reason argument
     * must leave whatever is already stored alone.
     */
    public function testItKeepsAnExistingReasonWhenNoNewReasonIsGiven()
    {
        $this->insertPayment(PaymentStatus::STATUS_OPEN, Config::WRONG_AMOUNT_REASON);

        $this->paymentMethodRepository->updateAttemptStatus(
            self::TRANSACTION_ID,
            PaymentStatus::STATUS_EXPIRED
        );

        $payment = $this->getPayment();

        $this->assertSame(PaymentStatus::STATUS_EXPIRED, $payment['bank_status']);
        $this->assertSame(Config::WRONG_AMOUNT_REASON, $payment['reason']);
    }

    /**
     * @dataProvider provideSuccessfulStatuses
     */
    public function testItRefusesToDowngradeASuccessfulPayment($status)
    {
        $this->insertPayment($status, 'kept');

        $result = $this->paymentMethodRepository->updateAttemptStatus(
            self::TRANSACTION_ID,
            PaymentStatus::STATUS_EXPIRED,
            'overwritten'
        );

        $this->assertFalse($result);

        $payment = $this->getPayment();

        $this->assertSame($status, $payment['bank_status']);
        $this->assertSame('kept', $payment['reason']);
    }

    public function provideSuccessfulStatuses()
    {
        return [
            'paid' => [PaymentStatus::STATUS_PAID],
            'authorized' => [PaymentStatus::STATUS_AUTHORIZED],
        ];
    }

    public function testItReportsFailureWhenTheTransactionIsUnknown()
    {
        $result = $this->paymentMethodRepository->updateAttemptStatus(
            'tr_does_not_exist',
            PaymentStatus::STATUS_FAILED,
            'invalid_card_number'
        );

        $this->assertFalse($result);
    }

    private function insertPayment($bankStatus, $reason = '', $orderId = 0)
    {
        Db::getInstance()->delete('mollie_payments', '`transaction_id` = \'' . pSQL(self::TRANSACTION_ID) . '\'');

        Db::getInstance()->insert('mollie_payments', [
            'transaction_id' => pSQL(self::TRANSACTION_ID),
            'cart_id' => 99999,
            'order_id' => (int) $orderId,
            'order_reference' => 'mol_pipres786',
            'method' => 'creditcard',
            'bank_status' => pSQL($bankStatus),
            'reason' => pSQL($reason),
            'created_at' => ['type' => 'sql', 'value' => 'NOW()'],
        ]);
    }

    private function getPayment()
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'mollie_payments` WHERE `transaction_id` = \''
            . pSQL(self::TRANSACTION_ID) . '\''
        );
    }
}
