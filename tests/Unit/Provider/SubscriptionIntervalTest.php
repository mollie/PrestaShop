<?php

namespace Mollie\Subscription\Tests\Unit\Provider;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Subscription\Config\Config;
use Mollie\Subscription\DTO\Object\Interval;
use Mollie\Subscription\Exception\SubscriptionIntervalException;
use Mollie\Subscription\Provider\SubscriptionIntervalProvider;
use PHPUnit\Framework\TestCase;

class SubscriptionIntervalTest extends TestCase
{
    /**
     * @dataProvider descriptionDataProvider
     */
    public function testGetSubscriptionInterval(array $attributeId, array $mockedGetResults, ?Interval $expectedInterval): void
    {
        $configurationMock = $this->createMock(ConfigurationAdapter::class);
        $configurationMock
            ->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValueMap($mockedGetResults)
            );
        $subscriptionIntervalProvider = new SubscriptionIntervalProvider($configurationMock);

        if ($expectedInterval === null) {
            $this->expectException(SubscriptionIntervalException::class);
        }
        $combination = $this->createMock('Combination');
        $combination->method('getWsProductOptionValues')->willReturn($attributeId);
        $description = $subscriptionIntervalProvider->getSubscriptionInterval($combination);

        $this->assertEquals($expectedInterval, $description);
    }

    public function descriptionDataProvider(): array
    {
        $langId = null;
        $shopGroupId = null;
        $shopId = null;

        $dailyProductAttributeId = 1;
        $weeklyProductAttributeId = 2;
        $monthlyProductAttributeId = 3;
        $basicProductAttribute = 4;

        return [
            'example daily' => [
                'attribute ids' => [
                    [
                        'id' => $dailyProductAttributeId,
                    ],
                ],
                'mocked get result' => [
                    [Config::SUBSCRIPTION_ATTRIBUTE_DAILY, $langId, $shopGroupId, $shopId, $dailyProductAttributeId],
                    [Config::SUBSCRIPTION_ATTRIBUTE_WEEKLY, $langId, $shopGroupId, $shopId, $weeklyProductAttributeId],
                    [Config::SUBSCRIPTION_ATTRIBUTE_MONTHLY, $langId, $shopGroupId, $shopId, $monthlyProductAttributeId],
                ],
                'expected result' => Config::getSubscriptionIntervals()[Config::SUBSCRIPTION_ATTRIBUTE_DAILY],
            ],
            'example weekly' => [
                'attribute ids' => [
                    [
                        'id' => $weeklyProductAttributeId,
                    ],
                ],
                'mocked get result' => [
                    [Config::SUBSCRIPTION_ATTRIBUTE_DAILY, $langId, $shopGroupId, $shopId, $dailyProductAttributeId],
                    [Config::SUBSCRIPTION_ATTRIBUTE_WEEKLY, $langId, $shopGroupId, $shopId, $weeklyProductAttributeId],
                    [Config::SUBSCRIPTION_ATTRIBUTE_MONTHLY, $langId, $shopGroupId, $shopId, $monthlyProductAttributeId],
                ],
                'expected result' => Config::getSubscriptionIntervals()[Config::SUBSCRIPTION_ATTRIBUTE_WEEKLY],
            ],
            'example monthly' => [
                'attribute ids' => [
                    ['id' => $monthlyProductAttributeId],
                    ['id' => $basicProductAttribute],
                ],
                'mocked get result' => [
                    [Config::SUBSCRIPTION_ATTRIBUTE_DAILY, $langId, $shopGroupId, $shopId, $dailyProductAttributeId],
                    [Config::SUBSCRIPTION_ATTRIBUTE_WEEKLY, $langId, $shopGroupId, $shopId, $weeklyProductAttributeId],
                    [Config::SUBSCRIPTION_ATTRIBUTE_MONTHLY, $langId, $shopGroupId, $shopId, $monthlyProductAttributeId],
                ],
                'expected result' => Config::getSubscriptionIntervals()[Config::SUBSCRIPTION_ATTRIBUTE_MONTHLY],
            ],
            'example unknown' => [
                'attribute ids' => [
                    ['id' => $basicProductAttribute],
                ],
                'mocked get result' => [
                    [Config::SUBSCRIPTION_ATTRIBUTE_DAILY, $langId, $shopGroupId, $shopId, $dailyProductAttributeId],
                    [Config::SUBSCRIPTION_ATTRIBUTE_WEEKLY, $langId, $shopGroupId, $shopId, $weeklyProductAttributeId],
                    [Config::SUBSCRIPTION_ATTRIBUTE_MONTHLY, $langId, $shopGroupId, $shopId, $monthlyProductAttributeId],
                ],
                'expected result' => null,
            ],
        ];
    }
}
