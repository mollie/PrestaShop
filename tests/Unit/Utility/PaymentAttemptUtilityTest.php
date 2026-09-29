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
use Mollie\Utility\PaymentAttemptUtility;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mollie\Utility\PaymentAttemptUtility
 */
class PaymentAttemptUtilityTest extends TestCase
{
    /**
     * @dataProvider provideFinalStatusResources
     *
     * @param mixed $apiResource
     */
    public function testItResolvesTheFinalStatus($apiResource, ?string $expected): void
    {
        $this->assertSame($expected, PaymentAttemptUtility::resolveFinalStatus($apiResource));
    }

    public function provideFinalStatusResources(): array
    {
        return [
            'payments_api_failed' => [
                $this->payment(PaymentStatus::STATUS_FAILED),
                PaymentStatus::STATUS_FAILED,
            ],
            'payments_api_canceled' => [
                $this->payment(PaymentStatus::STATUS_CANCELED),
                PaymentStatus::STATUS_CANCELED,
            ],
            'payments_api_expired' => [
                $this->payment(PaymentStatus::STATUS_EXPIRED),
                PaymentStatus::STATUS_EXPIRED,
            ],
            'payments_api_open_is_not_terminal' => [
                $this->payment(PaymentStatus::STATUS_OPEN),
                null,
            ],
            'payments_api_pending_is_not_terminal' => [
                $this->payment(PaymentStatus::STATUS_PENDING),
                null,
            ],
            'payments_api_paid_is_not_a_failure' => [
                $this->payment(PaymentStatus::STATUS_PAID),
                null,
            ],
            'orders_api_canceled_at_order_level' => [
                $this->order(OrderStatus::STATUS_CANCELED),
                OrderStatus::STATUS_CANCELED,
            ],
            'orders_api_expired_at_order_level' => [
                $this->order(OrderStatus::STATUS_EXPIRED),
                OrderStatus::STATUS_EXPIRED,
            ],
            // The Orders API has no failed status, so a declined card leaves the order
            // at created and only the embedded payment carries the failure.
            'orders_api_created_with_failed_last_payment' => [
                $this->order(OrderStatus::STATUS_CREATED, [
                    $this->payment(PaymentStatus::STATUS_FAILED),
                ]),
                PaymentStatus::STATUS_FAILED,
            ],
            'orders_api_created_with_open_last_payment' => [
                $this->order(OrderStatus::STATUS_CREATED, [
                    $this->payment(PaymentStatus::STATUS_OPEN),
                ]),
                null,
            ],
            'orders_api_last_embedded_payment_wins' => [
                $this->order(OrderStatus::STATUS_CREATED, [
                    $this->payment(PaymentStatus::STATUS_FAILED),
                    $this->payment(PaymentStatus::STATUS_CANCELED),
                    $this->payment(PaymentStatus::STATUS_EXPIRED),
                ]),
                PaymentStatus::STATUS_EXPIRED,
            ],
            'orders_api_open_order_still_reads_the_failed_payment' => [
                $this->order(OrderStatus::STATUS_PENDING, [
                    $this->payment(PaymentStatus::STATUS_FAILED),
                ]),
                PaymentStatus::STATUS_FAILED,
            ],
            'orders_api_paid_order_is_not_a_failure' => [
                $this->order(OrderStatus::STATUS_PAID, [
                    $this->payment(PaymentStatus::STATUS_PAID),
                ]),
                null,
            ],
            'orders_api_without_embedded_payments' => [
                $this->order(OrderStatus::STATUS_CREATED),
                null,
            ],
            'orders_api_with_empty_embedded_payments' => [
                $this->order(OrderStatus::STATUS_CREATED, []),
                null,
            ],
            // webhook.php re-fetches a tr_ payment as an Order resource whenever
            // $transaction->orderId is set, so the branch must key on resource.
            'payments_api_id_on_an_order_resource' => [
                $this->order(OrderStatus::STATUS_CREATED, [
                    $this->payment(PaymentStatus::STATUS_FAILED),
                ], 'tr_WDqYK6vllg'),
                PaymentStatus::STATUS_FAILED,
            ],
            'orders_api_id_on_a_payment_resource' => [
                $this->payment(PaymentStatus::STATUS_FAILED, null, 'ord_kEn1PlbGa'),
                PaymentStatus::STATUS_FAILED,
            ],
            'resource_without_a_status' => [
                (object) ['resource' => Config::MOLLIE_API_STATUS_PAYMENT],
                null,
            ],
            'object_without_a_resource_property' => [
                (object) ['status' => PaymentStatus::STATUS_FAILED],
                null,
            ],
            'null_resource' => [null, null],
            'scalar_resource' => ['failed', null],
        ];
    }

    /**
     * @dataProvider provideFailureReasonResources
     *
     * @param mixed $apiResource
     */
    public function testItResolvesTheFailureReason($apiResource, string $expected): void
    {
        $this->assertSame($expected, PaymentAttemptUtility::resolveFailureReason($apiResource));
    }

    public function provideFailureReasonResources(): array
    {
        return [
            'card_failure_reason' => [
                $this->payment(PaymentStatus::STATUS_FAILED, (object) ['failureReason' => 'invalid_card_number']),
                'invalid_card_number',
            ],
            'sepa_bank_reason_code' => [
                $this->payment(PaymentStatus::STATUS_FAILED, (object) ['bankReasonCode' => 'AM04']),
                'AM04',
            ],
            'failure_reason_wins_over_bank_reason_code' => [
                $this->payment(PaymentStatus::STATUS_FAILED, (object) [
                    'failureReason' => 'authentication_abandoned',
                    'bankReasonCode' => 'AM04',
                ]),
                'authentication_abandoned',
            ],
            'point_of_sale_status_reason' => [
                $this->posPayment('card_declined'),
                'card_declined',
            ],
            'details_without_a_reason' => [
                $this->payment(PaymentStatus::STATUS_FAILED, (object) ['cardNumber' => '1234']),
                '',
            ],
            'null_details' => [
                $this->payment(PaymentStatus::STATUS_FAILED),
                '',
            ],
            'orders_api_reads_the_last_embedded_payment' => [
                $this->order(OrderStatus::STATUS_CREATED, [
                    $this->payment(PaymentStatus::STATUS_FAILED, (object) ['failureReason' => 'insufficient_funds']),
                    $this->payment(PaymentStatus::STATUS_FAILED, (object) ['failureReason' => 'invalid_card_number']),
                ]),
                'invalid_card_number',
            ],
            'orders_api_without_embedded_payments' => [
                $this->order(OrderStatus::STATUS_CANCELED),
                '',
            ],
            'null_resource' => [null, ''],
        ];
    }

    public function testItTruncatesTheReasonToTheColumnWidth(): void
    {
        $reason = str_repeat('a', PaymentAttemptUtility::REASON_MAX_LENGTH + 10);

        $result = PaymentAttemptUtility::resolveFailureReason(
            $this->payment(PaymentStatus::STATUS_FAILED, (object) ['failureReason' => $reason])
        );

        $this->assertSame(PaymentAttemptUtility::REASON_MAX_LENGTH, strlen($result));
        $this->assertSame(str_repeat('a', PaymentAttemptUtility::REASON_MAX_LENGTH), $result);
    }

    public function testItKeepsTheLongestDocumentedReasonCodeIntact(): void
    {
        $result = PaymentAttemptUtility::resolveFailureReason(
            $this->payment(PaymentStatus::STATUS_FAILED, (object) ['failureReason' => 'authentication_unavailable_acs'])
        );

        $this->assertSame('authentication_unavailable_acs', $result);
    }

    private function payment(string $status, ?\stdClass $details = null, string $id = 'tr_WDqYK6vllg'): \stdClass
    {
        return (object) [
            'resource' => Config::MOLLIE_API_STATUS_PAYMENT,
            'id' => $id,
            'status' => $status,
            'details' => $details,
        ];
    }

    private function posPayment(string $code): \stdClass
    {
        $payment = $this->payment(PaymentStatus::STATUS_FAILED);
        $payment->statusReason = (object) ['code' => $code, 'message' => 'The card was declined'];

        return $payment;
    }

    private function order(string $status, ?array $payments = null, string $id = 'ord_kEn1PlbGa'): \stdClass
    {
        $order = (object) [
            'resource' => Config::MOLLIE_API_STATUS_ORDER,
            'id' => $id,
            'status' => $status,
        ];

        if (null !== $payments) {
            $order->_embedded = (object) ['payments' => $payments];
        }

        return $order;
    }
}
