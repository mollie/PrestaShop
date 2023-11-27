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

use Mollie\Subscription\Config\Config;
use Mollie\Subscription\Validator\SubscriptionOrderValidator;
use Mollie\Subscription\Validator\SubscriptionProductValidator;
use Mollie\Tests\Integration\BaseTestCase;

class SubscriptionOrderValidatorTest extends BaseTestCase
{
    private const NORMAL_PRODUCT_ATTRIBUTE_ID = 999;

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
    public function testValidate(array $orderProducts, $expectedResult): void
    {
        $cart = $this->createMock('Cart');

        $orderProductsMapped = array_map(function ($product) {
            return $this->getProducts($product);
        }, $orderProducts);

        $cart->method('getProducts')->willReturn($orderProductsMapped);

        $subscriptionProduct = $this->createMock(SubscriptionProductValidator::class);

        $mockedValidation = [
            [(int) $this->configuration->get(Config::SUBSCRIPTION_ATTRIBUTE_NONE), false],
            [(int) $this->configuration->get(Config::SUBSCRIPTION_ATTRIBUTE_DAILY), true],
            [(int) $this->configuration->get(Config::SUBSCRIPTION_ATTRIBUTE_WEEKLY), true],
            [(int) $this->configuration->get(Config::SUBSCRIPTION_ATTRIBUTE_MONTHLY), true],
            [self::NORMAL_PRODUCT_ATTRIBUTE_ID, false],
        ];

        $subscriptionProduct->method('validate')->will(
            $this->returnValueMap($mockedValidation)
        );

        $subscriptionOrderValidator = new SubscriptionOrderValidator($subscriptionProduct);

        $canBeAdded = $subscriptionOrderValidator->validate($cart);

        $this->assertEquals($expectedResult, $canBeAdded);
    }

    public function productDataProvider(): array
    {
        return [
            'One subscription product' => [
                'order products' => [
                    Config::SUBSCRIPTION_ATTRIBUTE_DAILY,
                ],
                'expected result' => true,
            ],
            'Two subscription products' => [
                'order products' => [
                    Config::SUBSCRIPTION_ATTRIBUTE_DAILY,
                    Config::SUBSCRIPTION_ATTRIBUTE_WEEKLY,
                ],
                'expected result' => false,
            ],
            'One subscription product and one normal product' => [
                'order products' => [
                    Config::SUBSCRIPTION_ATTRIBUTE_DAILY,
                    self::NORMAL_PRODUCT_ATTRIBUTE_ID,
                ],
                'expected result' => true,
            ],
            'Only normal product' => [
                'order products' => [
                    self::NORMAL_PRODUCT_ATTRIBUTE_ID,
                ],
                'expected result' => false,
            ],
        ];
    }

    private function getProducts(string $combinationReference): array
    {
        $combinationId = $this->configuration->get($combinationReference) ?: $combinationReference;

        return ['id_product_attribute' => $combinationId];
    }
}
