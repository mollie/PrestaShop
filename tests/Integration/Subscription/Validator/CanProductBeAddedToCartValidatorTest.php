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

namespace Mollie\Tests\Integration\Subscription\Validator;

use Mollie\Adapter\CartAdapter;
use Mollie\Adapter\ProductAttributeAdapter;
use Mollie\Subscription\Config\Config;
use Mollie\Subscription\Exception\SubscriptionProductValidationException;
use Mollie\Subscription\Repository\CombinationRepository;
use Mollie\Subscription\Repository\ProductCombinationRepository;
use Mollie\Subscription\Validator\CanProductBeAddedToCartValidator;
use Mollie\Subscription\Validator\SubscriptionProductValidator;
use Mollie\Tests\Integration\BaseTestCase;

class CanProductBeAddedToCartValidatorTest extends BaseTestCase
{
    private const NORMAL_PRODUCT_ATTRIBUTE_ID = 1;

    protected function setUp(): void
    {
        parent::setUp();

        $product = new \Product(1);

        $this->randomAttributeId = self::NORMAL_PRODUCT_ATTRIBUTE_ID;

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
    public function testValidate(string $combinationReference, bool $hasExtraAttribute, array $cartProducts, $expectedResult): void
    {
        $cartAdapterMock = $this->createMock(CartAdapter::class);

        $cartProducts = array_map(function (array $product) {
            return [
                'id_product_attribute' => $this->getCombination($product['id_product_attribute'], false),
            ];
        }, $cartProducts);

        $cartAdapterMock->method('getProducts')->willReturn($cartProducts);

        $combination = $this->getCombination($combinationReference, $hasExtraAttribute);

        $subscriptionCartValidator = new CanProductBeAddedToCartValidator(
            $cartAdapterMock,
            new SubscriptionProductValidator(
                $this->configuration,
                new ProductCombinationRepository(),
                new CombinationRepository(),
                new ProductAttributeAdapter()
            ),
            $this->getService(\Mollie\Adapter\ToolsAdapter::class)
        );

        if ($expectedResult !== true) {
            $this->expectException($expectedResult);
        }

        $canBeAdded = $subscriptionCartValidator->validate($combination);

        $this->assertEquals($expectedResult, $canBeAdded);
    }

    public function productDataProvider(): array
    {
        return [
            'One subscription product' => [
                'subscription reference' => Config::SUBSCRIPTION_ATTRIBUTE_DAILY,
                'has extra attribute' => false,
                'cart products' => [],
                'expected result' => true,
            ],
            'One normal product' => [
                'subscription reference' => '',
                'has extra attribute' => true,
                'cart products' => [],
                'expected result' => true,
            ],
            'Add subscription product but already have normal product in cart' => [
                'subscription reference' => Config::SUBSCRIPTION_ATTRIBUTE_DAILY,
                'has extra attribute' => false,
                'cart products' => [
                    [
                        'id_product_attribute' => self::NORMAL_PRODUCT_ATTRIBUTE_ID,
                    ],
                ],
                'expected result' => true,
            ],
            'Add subscription product but already have another subscription product in cart' => [
                'subscription reference' => Config::SUBSCRIPTION_ATTRIBUTE_DAILY,
                'has extra attribute' => false,
                'cart products' => [
                    [
                        'id_product_attribute' => Config::SUBSCRIPTION_ATTRIBUTE_MONTHLY,
                    ],
                ],
                'expected result' => SubscriptionProductValidationException::class,
            ],
            'Add normal product but already have another subscription product in cart' => [
                'subscription reference' => '',
                'has extra attribute' => true,
                'cart products' => [
                    [
                        'id_product_attribute' => Config::SUBSCRIPTION_ATTRIBUTE_MONTHLY,
                    ],
                ],
                'expected result' => true,
            ],
            'Add normal product but already have another normal product in cart' => [
                'subscription reference' => '',
                'has extra attribute' => true,
                'cart products' => [
                    [
                        'id_product_attribute' => self::NORMAL_PRODUCT_ATTRIBUTE_ID,
                    ],
                ],
                'expected result' => true,
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

        return (int) \Combination::getIdByReference(1, $reference);
    }
}
