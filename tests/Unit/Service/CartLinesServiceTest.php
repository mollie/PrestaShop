<?php

namespace Service;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\ToolsAdapter;
use Mollie\DTO\Line;
use Mollie\DTO\Object\Amount;
use Mollie\Repository\AttributeRepository;
use Mollie\Service\CartLinesService;
use Mollie\Service\LanguageService;
use Mollie\Service\VoucherService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CartLinesServiceTest extends TestCase
{
    /**
     * @dataProvider cartLinesProvider
     *
     * @param $amount
     * @param $paymentFee
     * @param $currencyIsoCode
     * @param $cartSummary
     * @param $shippingCost
     * @param $cartItems
     * @param $psGiftWrapping
     * @param $selectedVoucherCategory
     * @param $translationMocks
     * @param $toolsMocks
     * @param $mocks
     * @param $result
     */
    public function testGetCartLines(
        $amount,
        $paymentFee,
        $currencyIsoCode,
        $cartSummary,
        $shippingCost,
        $cartItems,
        $psGiftWrapping,
        $selectedVoucherCategory,
        $translationMocks,
        $toolsMocks,
        $mocks,
        $result
    ) {
        /** @var MockObject $configurationAdapter */
        $configurationAdapter = $this->getMockBuilder(ConfigurationAdapter::class)->getMock();
        foreach ($mocks as $mock) {
            $configurationAdapter->expects(self::at($mock['at']))->method($mock['function'])->with($mock['expects'])->willReturn($mock['return']);
        }

        /** @var MockObject $languageService */
        $languageService = $this->getMockBuilder(LanguageService::class)->disableOriginalConstructor()->getMock();
        foreach ($translationMocks as $mock) {
            $languageService->expects(self::at($mock['at']))->method($mock['function'])->with($mock['expects'])->willReturn($mock['return']);
        }

        /** @var ToolsAdapter $toolsAdapter */
        $toolsAdapter = $this->getMockBuilder(ToolsAdapter::class)->getMock();
        foreach ($toolsMocks as $mock) {
            $toolsAdapter->method($mock['function'])->with($mock['expects'])->willReturn($mock['return']);
        }

        $voucherService = new VoucherService(new AttributeRepository(), $configurationAdapter);

        $cartLineService = new CartLinesService($languageService, $voucherService, $toolsAdapter);
        $cartLines = $cartLineService->getCartLines(
            $amount,
            $paymentFee,
            $currencyIsoCode,
            $cartSummary,
            $shippingCost,
            $cartItems,
            $psGiftWrapping,
            $selectedVoucherCategory
        );

        self::assertEquals($result, $cartLines);
    }

    public function cartLinesProvider()
    {
        $productName_1 = 'Hummingbird printed sweater';
        $productName_2 = 'Hummingbird printed t-shirt';
        $productName_3 = 'The best is yet to come\' Framed poster';
        $shipping = 'Shipping';
        $giftWrapping = 'Gift wrapping';
        $currencyIsoCode = 'EUR';

        return [
            'two products with a gift which is the same as one product' => [
                'amount' => 204.84,
                'paymentFee' => false,
                'currencyIsoCode' => $currencyIsoCode,
                'cartSummary' => [
                    'gift_products' => [
                            0 => [
                                'id_product' => '1',
                                'cart_quantity' => 1,
                                'price_with_reduction' => 100,
                            ],
                        ],
                    'discounts' => [
                        ],
                    'total_wrapping' => 0,
                    'total_wrapping_tax_exc' => 0,
                    'total_shipping' => 4.84,
                    'total_shipping_tax_exc' => 4,
                    'total_products_wt' => 100,
                    'total_products' => 82.64,
                    'total_price' => 104.84,
                    'free_ship' => false,
                ],
                4.84,
                'cartItems' => [
                    0 => [
                            'total_wt' => 100,
                            'cart_quantity' => '1',
                            'price_wt' => 100,
                            'id_product' => '2',
                            'name' => $productName_1,
                            'rate' => 21,
                            'id_product_attribute' => '9',
                            'id_customization' => null,
                            'features' => [],
                        ],
                    1 => [
                            'total_wt' => 100,
                            'cart_quantity' => '2',
                            'price_wt' => 100,
                            'id_product' => '1',
                            'name' => $productName_2,
                            'rate' => 21,
                            'id_product_attribute' => '9',
                            'id_customization' => null,
                            'features' => [],
                        ],
                ],
                'psGiftWrapping' => '1',
                'selectedVoucherCategory' => 'null',
                'translationMocks' => [
                    0 => [
                        'function' => 'lang',
                        'expects' => $shipping,
                        'return' => $shipping,
                        'at' => 0,
                    ],
                ],
                'toolsMocks' => [
                    0 => [
                        'function' => 'strtoupper',
                        'expects' => $currencyIsoCode,
                        'return' => $currencyIsoCode,
                        'at' => 0,
                    ],
                ],
                'mocks' => [],
                'result' => [
                    0 => (new Line())
                            ->setName($productName_1)
                            ->setQuantity(1)
                            ->setSku('2¤9¤0')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount($currencyIsoCode, '100.00'))
                            ->setTotalPrice(new Amount($currencyIsoCode, '100.00'))
                            ->setVatAmount(new Amount($currencyIsoCode, '17.36'))
                            ->setCategory('')
                            ->setVatRate('21.00'),
                    1 => (new Line())
                        ->setName($productName_2)
                        ->setQuantity(1)
                        ->setSku('1¤9¤0gift')
                        ->setDiscountAmount(null)
                        ->setUnitPrice(new Amount($currencyIsoCode, '0.00'))
                        ->setTotalPrice(new Amount($currencyIsoCode, '0.00'))
                        ->setVatAmount(new Amount($currencyIsoCode, '0.00'))
                        ->setCategory('')
                        ->setVatRate('0.00'),
                    2 => (new Line())
                            ->setName($productName_2)
                            ->setQuantity(1)
                            ->setSku('1¤9¤0')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount($currencyIsoCode, '100.00'))
                            ->setTotalPrice(new Amount($currencyIsoCode, '100.00'))
                            ->setVatAmount(new Amount($currencyIsoCode, '17.36'))
                            ->setCategory('')
                            ->setVatRate('21.00'),
                    3 => (new Line())
                            ->setName($shipping)
                            ->setQuantity(1)
                            ->setSku('')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount($currencyIsoCode, '4.84'))
                            ->setTotalPrice(new Amount($currencyIsoCode, '4.84'))
                            ->setVatAmount(new Amount($currencyIsoCode, '0.84'))
                            ->setCategory(null)
                            ->setVatRate('21.00'),
                ],
            ],
            'one products with a gift' => [
                'amount' => 104.84,
                'paymentFee' => false,
                'currencyIsoCode' => $currencyIsoCode,
                'cartSummary' => [
                    'gift_products' => [
                            0 => [
                                'id_product' => '2',
                                'cart_quantity' => 1,
                                'price_with_reduction' => 100,
                            ],
                        ],
                    'discounts' => [
                        ],
                    'total_wrapping' => 0,
                    'total_wrapping_tax_exc' => 0,
                    'total_shipping' => 4.84,
                    'total_shipping_tax_exc' => 4,
                    'total_products_wt' => 100,
                    'total_products' => 82.64,
                    'total_price' => 104.84,
                    'free_ship' => false,
                ],
                4.84,
                'cartItems' => [
                    0 => [
                            'total_wt' => 100,
                            'cart_quantity' => '1',
                            'price_wt' => 100,
                            'id_product' => '1',
                            'name' => $productName_1,
                            'rate' => 21,
                            'id_product_attribute' => '9',
                            'id_customization' => null,
                            'features' => [],
                        ],
                    1 => [
                            'total_wt' => 100,
                            'cart_quantity' => '1',
                            'price_wt' => 100,
                            'id_product' => '2',
                            'name' => $productName_2,
                            'rate' => 21,
                            'id_product_attribute' => '9',
                            'id_customization' => null,
                            'features' => [],
                        ],
                ],
                'psGiftWrapping' => '1',
                'selectedVoucherCategory' => 'null',
                'translationMocks' => [
                    0 => [
                        'function' => 'lang',
                        'expects' => $shipping,
                        'return' => $shipping,
                        'at' => 0,
                    ],
                ],
                'toolsMocks' => [
                    0 => [
                        'function' => 'strtoupper',
                        'expects' => $currencyIsoCode,
                        'return' => $currencyIsoCode,
                        'at' => 0,
                    ],
                ],
                'mocks' => [],
                'result' => [
                    0 => (new Line())
                            ->setName($productName_1)
                            ->setQuantity(1)
                            ->setSku('1¤9¤0')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount($currencyIsoCode, '100.00'))
                            ->setTotalPrice(new Amount($currencyIsoCode, '100.00'))
                            ->setVatAmount(new Amount($currencyIsoCode, '17.36'))
                            ->setCategory('')
                            ->setVatRate('21.00'),
                    1 => (new Line())
                            ->setName($productName_2)
                            ->setQuantity(1)
                            ->setSku('2¤9¤0gift')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount($currencyIsoCode, '0.00'))
                            ->setTotalPrice(new Amount($currencyIsoCode, '0.00'))
                            ->setVatAmount(new Amount($currencyIsoCode, '0.00'))
                            ->setCategory('')
                            ->setVatRate('0.00'),
                    2 => (new Line())
                            ->setName($shipping)
                            ->setQuantity(1)
                            ->setSku('')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount($currencyIsoCode, '4.84'))
                            ->setTotalPrice(new Amount($currencyIsoCode, '4.84'))
                            ->setVatAmount(new Amount($currencyIsoCode, '0.84'))
                            ->setCategory(null)
                            ->setVatRate('21.00'),
                ],
            ],
            'product without name' => [
                'amount' => 104.84,
                'paymentFee' => false,
                'currencyIsoCode' => $currencyIsoCode,
                'cartSummary' => [
                    'gift_products' => [
                            0 => [
                                'id_product' => '1',
                                'cart_quantity' => 1,
                                'price_with_reduction' => 100,
                            ],
                        ],
                    'discounts' => [
                        ],
                    'total_wrapping' => 0,
                    'total_wrapping_tax_exc' => 0,
                    'total_shipping' => 4.84,
                    'total_shipping_tax_exc' => 4,
                    'total_products_wt' => 100,
                    'total_products' => 82.64,
                    'total_price' => 104.84,
                    'free_ship' => false,
                ],
                4.84,
                'cartItems' => [
                    0 => [
                            'total_wt' => 100,
                            'cart_quantity' => '1',
                            'price_wt' => 100,
                            'id_product' => '2',
                            'name' => '',
                            'rate' => 21,
                            'id_product_attribute' => '9',
                            'id_customization' => null,
                            'features' => [],
                        ],
                ],
                'psGiftWrapping' => '1',
                'selectedVoucherCategory' => 'null',
                'translationMocks' => [
                    0 => [
                        'function' => 'lang',
                        'expects' => $shipping,
                        'return' => $shipping,
                        'at' => 0,
                    ],
                ],
                'toolsMocks' => [
                    0 => [
                        'function' => 'strtoupper',
                        'expects' => $currencyIsoCode,
                        'return' => $currencyIsoCode,
                        'at' => 0,
                    ],
                ],
                'mocks' => [],
                'result' => [
                    0 => (new Line())
                            ->setName('2¤9¤0')
                            ->setQuantity(1)
                            ->setSku('2¤9¤0')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount($currencyIsoCode, '100.00'))
                            ->setTotalPrice(new Amount($currencyIsoCode, '100.00'))
                            ->setVatAmount(new Amount($currencyIsoCode, '17.36'))
                            ->setCategory('')
                            ->setVatRate('21.00'),
                    1 => (new Line())
                            ->setName($shipping)
                            ->setQuantity(1)
                            ->setSku('')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount($currencyIsoCode, '4.84'))
                            ->setTotalPrice(new Amount($currencyIsoCode, '4.84'))
                            ->setVatAmount(new Amount($currencyIsoCode, '0.84'))
                            ->setCategory(null)
                            ->setVatRate('21.00'),
                ],
            ],
            'Cart with discount' => [
                'amount' => 98.79,
                'paymentFee' => false,
                'currencyIsoCode' => $currencyIsoCode,
                'cartSummary' => [
                    'gift_products' => [
                        ],
                    'discounts' => [
                        ],
                    'total_wrapping' => 0,
                    'total_wrapping_tax_exc' => 0,
                    'total_shipping' => 4.84,
                    'total_shipping_tax_exc' => 4,
                    'total_products_wt' => 100,
                    'total_products' => 82.64,
                    'total_price' => 98.79,
                    'free_ship' => false,
                    'total_discounts' => 6.05,
                ],
                4.84,
                'cartItems' => [
                    0 => [
                            'total_wt' => 100,
                            'cart_quantity' => '1',
                            'price_wt' => 100,
                            'id_product' => '2',
                            'name' => $productName_1,
                            'rate' => 21,
                            'id_product_attribute' => '9',
                            'id_customization' => null,
                            'features' => [],
                        ],
                ],
                'psGiftWrapping' => '1',
                'selectedVoucherCategory' => 'null',
                'translationMocks' => [
                    0 => [
                        'function' => 'lang',
                        'expects' => $shipping,
                        'return' => $shipping,
                        'at' => 0,
                    ],
                ],
                'toolsMocks' => [
                    0 => [
                        'function' => 'strtoupper',
                        'expects' => $currencyIsoCode,
                        'return' => $currencyIsoCode,
                        'at' => 0,
                    ],
                ],
                'mocks' => [],
                'result' => [
                    0 => (new Line())
                            ->setName($productName_1)
                            ->setQuantity(1)
                            ->setSku('2¤9¤0')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount($currencyIsoCode, '100.00'))
                            ->setTotalPrice(new Amount($currencyIsoCode, '100.00'))
                            ->setVatAmount(new Amount($currencyIsoCode, '17.36'))
                            ->setCategory(null)
                            ->setVatRate('21.00'),
                    1 => (new Line())
                            ->setName('Discount')
                            ->setQuantity(1)
                            ->setSku('')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount($currencyIsoCode, '-6.05'))
                            ->setTotalPrice(new Amount($currencyIsoCode, '-6.05'))
                            ->setVatAmount(new Amount($currencyIsoCode, '0.00'))
                            ->setCategory(null)
                            ->setVatRate('0.00'),
                    2 => (new Line())
                            ->setName($shipping)
                            ->setQuantity(1)
                            ->setSku('')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount($currencyIsoCode, '4.84'))
                            ->setTotalPrice(new Amount($currencyIsoCode, '4.84'))
                            ->setVatAmount(new Amount($currencyIsoCode, '0.84'))
                            ->setCategory(null)
                            ->setVatRate('21.00'),
                ],
            ],
        ];
    }
}
