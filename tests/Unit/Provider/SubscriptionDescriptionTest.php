<?php

namespace Mollie\Subscription\Tests\Unit\Provider;

use Mollie\Subscription\Provider\SubscriptionDescriptionProvider;
use PHPUnit\Framework\TestCase;

class subscriptionDescriptionTest extends TestCase
{
    /**
     * @dataProvider descriptionDataProvider
     */
    public function testGetSubscriptionDescription(int $orderId, string $orderReference, string $expectedDescription): void
    {
        $orderMock = $this->createMock('Order');
        $orderMock->id = $orderId;
        $orderMock->reference = $orderReference;

        $subscriptionDescriptionProvider = new SubscriptionDescriptionProvider();

        $description = $subscriptionDescriptionProvider->getSubscriptionDescription($orderMock);

        $this->assertEquals($expectedDescription, $description);
    }

    public function descriptionDataProvider(): array
    {
        return [
            'example 1' => [
                'order id' => 1,
                'order reference' => '123aaa',
                'expected result' => 'subscription-123aaa',
            ],
            'example 2' => [
                'order id' => 9999990,
                'order reference' => '123bbb',
                'expected result' => 'subscription-123bbb',
            ],
        ];
    }
}
