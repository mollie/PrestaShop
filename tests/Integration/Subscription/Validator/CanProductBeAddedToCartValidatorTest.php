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
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\ToolsAdapter;
use Mollie\Config\Config as SettingsConfig;
use Mollie\Subscription\Config\Config;
use Mollie\Subscription\Exception\ExceptionCode;
use Mollie\Subscription\Exception\SubscriptionProductValidationException;
use Mollie\Subscription\Provider\SubscriptionProductProvider;
use Mollie\Subscription\Validator\CanProductBeAddedToCartValidator;
use Mollie\Subscription\Validator\SubscriptionProductValidator;
use Mollie\Subscription\Validator\SubscriptionSettingsValidator;
use Mollie\Tests\Integration\BaseTestCase;
use Mollie\Tests\Integration\Factory\CarrierFactory;

class CanProductBeAddedToCartValidatorTest extends BaseTestCase
{
    private const NORMAL_PRODUCT_ATTRIBUTE_ID = 1;

    /** @var \Carrier */
    private $validCarrier;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->validCarrier = CarrierFactory::create();
    }

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
    public function testValidate(
        string $combinationReference,
        bool $hasExtraAttribute,
        array $cartProducts,
        bool $subscriptionEnabled,
        int $subscriptionCarrierId,
        bool $expectedResult,
        string $expectedException,
        int $expectedExceptionCode
    ): void {
        // TODO cart factory
        $cart = $this->createMock(CartAdapter::class);

        $cartProducts = array_map(function (array $product) {
            return [
                'id_product_attribute' => $this->getCombination($product['id_product_attribute'], false),
            ];
        }, $cartProducts);

        $cart->method('getProducts')->willReturn($cartProducts);

        $combination = $this->getCombination($combinationReference, $hasExtraAttribute);

        /** @var ConfigurationAdapter $configuration */
        $configuration = $this->getService(ConfigurationAdapter::class);

        $configuration->updateValue(SettingsConfig::MOLLIE_SUBSCRIPTION_ENABLED, $subscriptionEnabled);
        $configuration->updateValue(SettingsConfig::MOLLIE_SUBSCRIPTION_ORDER_CARRIER_ID, $subscriptionCarrierId);

        $subscriptionCartValidator = new CanProductBeAddedToCartValidator(
            $cart,
            $this->getService(SubscriptionProductValidator::class),
            $this->getService(ToolsAdapter::class),
            $this->getService(SubscriptionSettingsValidator::class),
            $this->getService(SubscriptionProductProvider::class)
        );

        if (!$expectedResult) {
            $this->expectException($expectedException);
            $this->expectExceptionCode($expectedExceptionCode);
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
                'subscription enabled' => true,
                'subscription carrier ID' => $this->validCarrier->id,
                'expected result' => true,
                'expected exception' => '',
                'expected exception code' => 0,
            ],
            'One subscription product disabled subscription' => [
                'subscription reference' => Config::SUBSCRIPTION_ATTRIBUTE_DAILY,
                'has extra attribute' => false,
                'cart products' => [],
                'subscription enabled' => false,
                'subscription carrier ID' => $this->validCarrier->id,
                'expected result' => false,
                'expected exception' => SubscriptionProductValidationException::class,
                'expected exception code' => ExceptionCode::CART_INVALID_SUBSCRIPTION_SETTINGS,
            ],
            'One subscription product invalid carrier' => [
                'subscription reference' => Config::SUBSCRIPTION_ATTRIBUTE_DAILY,
                'has extra attribute' => false,
                'cart products' => [],
                'subscription enabled' => true,
                'subscription carrier ID' => 0,
                'expected result' => false,
                'expected exception' => SubscriptionProductValidationException::class,
                'expected exception code' => ExceptionCode::CART_INVALID_SUBSCRIPTION_SETTINGS,
            ],
            'One normal product' => [
                'subscription reference' => '',
                'has extra attribute' => true,
                'cart products' => [],
                'subscription enabled' => true,
                'subscription carrier ID' => $this->validCarrier->id,
                'expected result' => true,
                'expected exception' => '',
                'expected exception code' => 0,
            ],
            'One normal product disabled subscription and invalid carrier' => [
                'subscription reference' => '',
                'has extra attribute' => true,
                'cart products' => [],
                'subscription enabled' => false,
                'subscription carrier ID' => 0,
                'expected result' => true,
                'expected exception' => '',
                'expected exception code' => 0,
            ],
            'Add subscription product but already have normal product in cart' => [
                'subscription reference' => Config::SUBSCRIPTION_ATTRIBUTE_DAILY,
                'has extra attribute' => false,
                'cart products' => [
                    [
                        'id_product_attribute' => self::NORMAL_PRODUCT_ATTRIBUTE_ID,
                    ],
                ],
                'subscription enabled' => true,
                'subscription carrier ID' => $this->validCarrier->id,
                'expected result' => true,
                'expected exception' => '',
                'expected exception code' => 0,
            ],
            'Add subscription product but already have another subscription product in cart' => [
                'subscription reference' => Config::SUBSCRIPTION_ATTRIBUTE_DAILY,
                'has extra attribute' => false,
                'cart products' => [
                    [
                        'id_product_attribute' => Config::SUBSCRIPTION_ATTRIBUTE_MONTHLY,
                    ],
                ],
                'subscription enabled' => true,
                'subscription carrier ID' => $this->validCarrier->id,
                'expected result' => false,
                'expected exception' => SubscriptionProductValidationException::class,
                'expected exception code' => ExceptionCode::CART_ALREADY_HAS_SUBSCRIPTION_PRODUCT,
            ],
            'Add normal product but already have another subscription product in cart' => [
                'subscription reference' => '',
                'has extra attribute' => true,
                'cart products' => [
                    [
                        'id_product_attribute' => Config::SUBSCRIPTION_ATTRIBUTE_MONTHLY,
                    ],
                ],
                'subscription enabled' => true,
                'subscription carrier ID' => $this->validCarrier->id,
                'expected result' => true,
                'expected exception' => '',
                'expected exception code' => 0,
            ],
            'Add normal product but already have another normal product in cart' => [
                'subscription reference' => '',
                'has extra attribute' => true,
                'cart products' => [
                    [
                        'id_product_attribute' => self::NORMAL_PRODUCT_ATTRIBUTE_ID,
                    ],
                ],
                'subscription enabled' => true,
                'subscription carrier ID' => $this->validCarrier->id,
                'expected result' => true,
                'expected exception' => '',
                'expected exception code' => 0,
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
