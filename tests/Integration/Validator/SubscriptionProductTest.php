<?php

namespace Mollie\Subscription\Tests\Integration\Validator;

use Combination;
use Mollie\Subscription\Config\Config;
use Mollie\Subscription\Tests\Integration\BaseTestCase;
use Mollie\Subscription\Validator\SubscriptionProduct;
use Product;

class SubscriptionProductTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $product = new Product(1);

        $this->randomAttributeId = 1;

        $attributeCombinations = [
            [
                $this->configuration->get(Config::SUBSCRIPTION_ATTRIBUTE_NONE),
            ],
            [
                $this->configuration->get(Config::SUBSCRIPTION_ATTRIBUTE_DAILY),
            ],
            [
                $this->configuration->get(Config::SUBSCRIPTION_ATTRIBUTE_WEEKLY),
            ],
            [
                $this->configuration->get(Config::SUBSCRIPTION_ATTRIBUTE_MONTHLY),
                $this->randomAttributeId,
            ],
            [
                $this->randomAttributeId,
            ],
        ];

        $combinations = [
            'id_product' => $product->id,
        ];

        foreach ($attributeCombinations as $attributes) {
            $combinations['reference'] = implode('-', $attributes);
            $product->generateMultipleCombinations([$combinations], [$attributes]);
        }
    }

    /**
     * @dataProvider productDataProvider
     */
    public function testValidate(string $combinationReference, bool $hasExtraAttribute, bool $expectedResult): void
    {
        $combination = $this->getCombination($combinationReference, $hasExtraAttribute);
        $subscriptionProductValidator = $this->getService(SubscriptionProduct::class);
        $isSubscriptionProduct = $subscriptionProductValidator->validate($combination);

        $this->assertEquals($expectedResult, $isSubscriptionProduct);
    }

    public function productDataProvider(): array
    {
        return [
            'subscription product none' => [
                'subscription reference' => Config::SUBSCRIPTION_ATTRIBUTE_NONE,
                'has extra attribute' => false,
                'expected result' => false,
            ],
            'subscription product daily' => [
                'subscription reference' => Config::SUBSCRIPTION_ATTRIBUTE_DAILY,
                'has extra attribute' => false,
                'expected result' => true,
            ],
            'subscription product monthly and random attribute' => [
                'subscription reference' => Config::SUBSCRIPTION_ATTRIBUTE_MONTHLY,
                'has extra attribute' => true,
                'expected result' => true,
            ],
            'only random attribute' => [
                'subscription reference' => '',
                'has extra attribute' => true,
                'expected result' => false,
            ],
        ];
    }

    private function getCombination(string $combinationReference, bool $hasExtraAttribute): int
    {
        $reference = $this->configuration->get($combinationReference);
        if ($hasExtraAttribute) {
            $reference = $reference ? implode('-', [
                $this->configuration->get($combinationReference),
                $this->randomAttributeId,
            ]) : $this->randomAttributeId;
        }

        return (int) Combination::getIdByReference(1, $reference);
    }
}
