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

use Mollie\Api\Types\PaymentStatus;
use Mollie\Service\PayByBankCancellationService;
use PHPUnit\Framework\TestCase;

class PayByBankCancellationServiceTest extends TestCase
{
    /**
     * @dataProvider shouldCancelPaymentDataProvider
     *
     * @param string $status
     * @param bool $expected
     */
    public function testShouldCancelPayment($status, $expected)
    {
        $service = $this->createRealServiceForPureMethods();

        self::assertEquals($expected, $service->shouldCancelPayment($status));
    }

    public function shouldCancelPaymentDataProvider()
    {
        return [
            'canceled status' => [
                'status' => PaymentStatus::STATUS_CANCELED,
                'expected' => true,
            ],
            'expired status' => [
                'status' => PaymentStatus::STATUS_EXPIRED,
                'expected' => true,
            ],
            'failed status' => [
                'status' => PaymentStatus::STATUS_FAILED,
                'expected' => true,
            ],
            'open status' => [
                'status' => PaymentStatus::STATUS_OPEN,
                'expected' => true,
            ],
            'pending status' => [
                'status' => PaymentStatus::STATUS_PENDING,
                'expected' => false,
            ],
            'paid status' => [
                'status' => PaymentStatus::STATUS_PAID,
                'expected' => false,
            ],
            'authorized status' => [
                'status' => PaymentStatus::STATUS_AUTHORIZED,
                'expected' => false,
            ],
            'empty string (API error)' => [
                'status' => '',
                'expected' => false,
            ],
        ];
    }

    /**
     * @dataProvider resolveCancelStatusDataProvider
     *
     * @param string $input
     * @param string $expected
     */
    public function testResolveCancelStatus($input, $expected)
    {
        $service = $this->createRealServiceForPureMethods();

        self::assertEquals($expected, $service->resolveCancelStatus($input));
    }

    public function resolveCancelStatusDataProvider()
    {
        return [
            'open maps to canceled' => [
                'input' => PaymentStatus::STATUS_OPEN,
                'expected' => PaymentStatus::STATUS_CANCELED,
            ],
            'canceled passes through' => [
                'input' => PaymentStatus::STATUS_CANCELED,
                'expected' => PaymentStatus::STATUS_CANCELED,
            ],
            'expired passes through' => [
                'input' => PaymentStatus::STATUS_EXPIRED,
                'expected' => PaymentStatus::STATUS_EXPIRED,
            ],
            'failed passes through' => [
                'input' => PaymentStatus::STATUS_FAILED,
                'expected' => PaymentStatus::STATUS_FAILED,
            ],
        ];
    }

    /**
     * @dataProvider isTerminalFailureDataProvider
     *
     * @param string $status
     * @param bool $expected
     */
    public function testIsTerminalFailure($status, $expected)
    {
        $service = $this->createRealServiceForPureMethods();

        self::assertEquals($expected, $service->isTerminalFailure($status));
    }

    public function isTerminalFailureDataProvider()
    {
        return [
            'canceled' => [PaymentStatus::STATUS_CANCELED, true],
            'expired' => [PaymentStatus::STATUS_EXPIRED, true],
            'failed' => [PaymentStatus::STATUS_FAILED, true],
            'open' => [PaymentStatus::STATUS_OPEN, false],
            'pending' => [PaymentStatus::STATUS_PENDING, false],
            'paid' => [PaymentStatus::STATUS_PAID, false],
            'authorized' => [PaymentStatus::STATUS_AUTHORIZED, false],
            'empty' => ['', false],
        ];
    }

    private function createRealServiceForPureMethods()
    {
        return $this->getMockBuilder(PayByBankCancellationService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getActualMollieStatus', 'cancelOrderAndRestoreCart'])
            ->getMock();
    }
}
