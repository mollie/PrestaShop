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

namespace Mollie\Tests\Unit\Builder;

use Mollie\Adapter\ToolsAdapter;
use Mollie\Builder\PaymentHistoryBuilder;
use Mollie\Tests\Unit\BaseTestCase;

class PaymentHistoryBuilderTest extends BaseTestCase
{
    /** @var string */
    private $previousTimezone;

    /** @var PaymentHistoryBuilder */
    private $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->previousTimezone = date_default_timezone_get();
        date_default_timezone_set('Europe/Amsterdam');

        $tools = $this->mock(ToolsAdapter::class);
        $tools->method('displayDate')->willReturnArgument(0);
        $tools->method('displayPrice')->willReturnCallback(function ($price, $currency = null) {
            return $currency->iso_code . ' ' . number_format($price, 2, '.', '');
        });

        $this->builder = new PaymentHistoryBuilder($tools);
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->previousTimezone);

        parent::tearDown();
    }

    public function testOpenPaymentProducesSingleCreatedEvent(): void
    {
        $payment = (object) [
            'createdAt' => '2026-10-12T10:00:00+00:00',
            'amount' => $this->amount('12.34'),
        ];

        $events = $this->builder->build($payment);

        $this->assertCount(1, $events);
        $this->assertSame('created', $events[0]['type']);
        $this->assertSame('12.34', $events[0]['amount']);
        $this->assertSame('EUR', $events[0]['currency']);
        $this->assertSame('EUR 12.34', $events[0]['amount_formatted']);
        $this->assertFalse($events[0]['negative']);
        $this->assertSame('2026-10-12 12:00:00', $events[0]['sort_date']);
        $this->assertSame('2026-10-12 12:00:00', $events[0]['date']);
    }

    public function testPaidPaymentWithRefundsAndCaptureIsSortedNewestFirst(): void
    {
        $payment = (object) [
            'createdAt' => '2026-10-12T10:00:00+00:00',
            'paidAt' => '2026-10-12T10:00:00+00:00',
            'amount' => $this->amount('100.00'),
            'settlementAmount' => $this->amount('98.50'),
            '_embedded' => (object) [
                'refunds' => [
                    (object) [
                        'id' => 're_first',
                        'amount' => $this->amount('10.00'),
                        'createdAt' => '2026-10-12T11:00:00+00:00',
                        'status' => 'refunded',
                    ],
                    (object) [
                        'id' => 're_second',
                        'amount' => $this->amount('5.00'),
                        'createdAt' => '2026-10-12T13:00:00+00:00',
                        'status' => 'pending',
                    ],
                ],
                'captures' => [
                    (object) [
                        'id' => 'cpt_one',
                        'amount' => $this->amount('100.00'),
                        'createdAt' => '2026-10-12T10:30:00+00:00',
                        'status' => 'succeeded',
                    ],
                ],
            ],
        ];

        $events = $this->builder->build($payment);

        $this->assertSame(
            ['refunded', 'refunded', 'captured', 'paid', 'created'],
            array_column($events, 'type')
        );
        $this->assertNotContains('authorized', array_column($events, 'type'));
        $this->assertSame('pending', $events[0]['status']);
        $this->assertSame('re_second', $events[0]['reference']);
        $this->assertSame('5.00', $events[0]['amount']);
        $this->assertTrue($events[0]['negative']);
        $this->assertSame('-EUR 5.00', $events[0]['amount_formatted']);
        $this->assertSame('refunded', $events[1]['status']);
        $this->assertSame('re_first', $events[1]['reference']);
        $this->assertSame('cpt_one', $events[2]['reference']);
        $this->assertSame('succeeded', $events[2]['status']);
        $this->assertFalse($events[2]['negative']);
        $this->assertFalse($events[3]['negative']);
        $this->assertSame('EUR 100.00', $events[3]['amount_formatted']);
        $this->assertSame(
            [['label' => 'settlement_amount', 'value' => 'EUR 98.50']],
            $events[3]['details']
        );
    }

    public function testCapturedPaymentFallsBackToOrderHistoryForAuthorizedDate(): void
    {
        $payment = (object) [
            'createdAt' => '2026-10-12T06:42:00+00:00',
            'paidAt' => '2026-10-12T07:00:05+00:00',
            'captureMode' => 'manual',
            'amount' => $this->amount('100.00'),
            '_embedded' => (object) [
                'captures' => [
                    (object) [
                        'id' => 'cpt_one',
                        'amount' => $this->amount('100.00'),
                        'createdAt' => '2026-10-12T07:00:00+00:00',
                        'status' => 'succeeded',
                    ],
                ],
            ],
        ];

        $events = $this->builder->build($payment, '2026-10-12 08:42:50');

        $this->assertSame(
            ['paid', 'captured', 'authorized', 'created'],
            array_column($events, 'type')
        );
        $this->assertSame('2026-10-12 08:42:50', $events[2]['sort_date']);
        $this->assertSame(
            [['label' => 'capture_mode', 'value' => 'manual']],
            $events[2]['details']
        );
    }

    public function testExpiredPaymentPutsExpiredFirst(): void
    {
        $payment = (object) [
            'createdAt' => '2026-10-12T10:00:00+00:00',
            'expiredAt' => '2026-10-12T10:15:00+00:00',
            'amount' => $this->amount('12.34'),
        ];

        $events = $this->builder->build($payment);

        $this->assertSame(['expired', 'created'], array_column($events, 'type'));
        $this->assertSame('12.34', $events[0]['amount']);
    }

    public function testChargebackWithReversalProducesBothEvents(): void
    {
        $payment = (object) [
            'createdAt' => '2026-10-10T10:00:00+00:00',
            'paidAt' => '2026-10-10T10:01:00+00:00',
            'amount' => $this->amount('50.00'),
            '_embedded' => (object) [
                'chargebacks' => [
                    (object) [
                        'id' => 'chb_one',
                        'amount' => $this->amount('50.00'),
                        'createdAt' => '2026-10-11T09:00:00+00:00',
                        'reversedAt' => '2026-10-12T09:00:00+00:00',
                        'reason' => (object) [
                            'code' => 'AC01',
                            'description' => 'Account identifier incorrect',
                        ],
                    ],
                ],
            ],
        ];

        $events = $this->builder->build($payment);

        $this->assertSame(
            ['chargeback_reversed', 'chargeback', 'paid', 'created'],
            array_column($events, 'type')
        );
        $this->assertSame('chb_one', $events[0]['reference']);
        $this->assertSame('50.00', $events[0]['amount']);
        $this->assertTrue($events[0]['negative']);
        $this->assertSame('-EUR 50.00', $events[1]['amount_formatted']);
        $this->assertSame('chb_one', $events[1]['reference']);
        $this->assertSame(
            [['label' => 'reason', 'value' => 'Account identifier incorrect']],
            $events[1]['details']
        );
    }

    public function testNullEmbeddedCollectionAndDueDateDoNotBreak(): void
    {
        $payment = (object) [
            'createdAt' => '2026-10-12T10:00:00+00:00',
            'amount' => $this->amount('20.00'),
            'method' => 'banktransfer',
            'dueDate' => '2026-10-20',
            '_embedded' => (object) [
                'refunds' => null,
            ],
        ];

        $events = $this->builder->build($payment);

        $this->assertCount(1, $events);
        $this->assertSame('created', $events[0]['type']);
        $this->assertSame(
            [
                ['label' => 'method', 'value' => 'banktransfer'],
                ['label' => 'due_date', 'value' => '2026-10-20'],
            ],
            $events[0]['details']
        );
    }

    public function testNegativeSettlementAmountKeepsSingleMinusSign(): void
    {
        $payment = (object) [
            'createdAt' => '2026-10-12T10:00:00+00:00',
            'amount' => $this->amount('20.00'),
            '_embedded' => (object) [
                'refunds' => [
                    (object) [
                        'id' => 're_one',
                        'amount' => $this->amount('20.00'),
                        'settlementAmount' => $this->amount('-20.00'),
                        'createdAt' => '2026-10-12T11:00:00+00:00',
                        'status' => 'refunded',
                    ],
                ],
            ],
        ];

        $events = $this->builder->build($payment);

        $this->assertSame('-EUR 20.00', $events[0]['amount_formatted']);
        $this->assertSame(
            [['label' => 'settlement_amount', 'value' => '-EUR 20.00']],
            $events[0]['details']
        );
    }

    private function amount(string $value): \stdClass
    {
        return (object) ['value' => $value, 'currency' => 'EUR'];
    }
}
