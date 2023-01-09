<?php

namespace Mollie\Subscription\Tests\Unit\Provider;

use Mollie\Subscription\Provider\SubscriptionDescription;
use PHPUnit\Framework\TestCase;

class subscriptionDescriptionTest extends TestCase
{
    /**
     * @dataProvider descriptionDataProvider
     */
    public function testGetSubscriptionDescription(int $orderId, float $totalPaid, string $currencyIso, string $expectedDescription): void
    {
        $orderMock = $this->createMock('Order');
        $orderMock->id = $orderId;
        $orderMock->total_paid_tax_incl = $totalPaid;

        $subscriptionDescriptionProvider = new SubscriptionDescription();

        $description = $subscriptionDescriptionProvider->getSubscriptionDescription($orderMock, $currencyIso);

        $this->assertEquals($expectedDescription, $description);
    }

    public function descriptionDataProvider(): array
    {
        return [
            'example 1' => [
                'order id' => 1,
                'total paid' => 19.99,
                'currency iso code' => 'EUR',
                'expected result' => 'mol-1-19.99-EUR',
            ],
            'example 2' => [
                'order id' => 9999990,
                'total paid' => 20.5,
                'currency iso code' => 'USD',
                'expected result' => 'mol-9999990-20.5-USD',
            ],
        ];
    }
}
