<?php

namespace Service;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\ToolsAdapter;
use Mollie\Config\Config;
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
            'one product with default no voucher category' => [
                'amount' => 104.84,
                'paymentFee' => false,
                'currencyIsoCode' => $currencyIsoCode,
                'cartSummary' => [

                    'gift_products' =>
                        [
                        ],
                    'discounts' =>
                        [
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
                    0 =>
                        [
                            'total_wt' => 100,
                            'cart_quantity' => '1',
                            'price_wt' => 100,
                            'id_product' => '2',
                            'name' => $productName_1,
                            'rate' => 21,
                            'id_product_attribute' => '9',
                            'id_customization' => NULL,
                            'features' => []
                        ],
                ],
                'psGiftWrapping' => '1',
                'selectedVoucherCategory' => 'null',
                'translationMocks' => [
                    0 => [
                        'function' => 'lang',
                        'expects' => $shipping,
                        'return' => $shipping,
                        'at' => 0
                    ]
                ],      
                'toolsMocks' => [
                    0 => [
                        'function' => 'strtoupper',
                        'expects' => $currencyIsoCode,
                        'return' => $currencyIsoCode,
                        'at' => 0
                    ]
                ],
                'mocks' => [],
                'result' => [
                    0 =>
                        (new Line())
                            ->setName($productName_1)
                            ->setQuantity(1)
                            ->setSku('2¤9¤0')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount($currencyIsoCode, '100.00'))
                            ->setTotalPrice(new Amount($currencyIsoCode, '100.00'))
                            ->setVatAmount(new Amount($currencyIsoCode, '17.36'))
                            ->setCategory(null)
                            ->setVatRate('21.00'),
                    1 =>
                        (new Line())
                            ->setName($shipping)
                            ->setQuantity(1)
                            ->setSku('')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount($currencyIsoCode, '4.84'))
                            ->setTotalPrice(new Amount($currencyIsoCode, '4.84'))
                            ->setVatAmount(new Amount($currencyIsoCode, '0.84'))
                            ->setCategory(null)
                            ->setVatRate('21.00'),
                ]
            ],
            'one product with default meal category' => [
                'amount' => 104.84,
                'paymentFee' => false,
                'currencyIsoCode' => $currencyIsoCode,
                'cartSummary' => [

                    'gift_products' =>
                        [
                        ],
                    'discounts' =>
                        [
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
                    0 =>
                        [
                            'total_wt' => 100,
                            'cart_quantity' => '1',
                            'price_wt' => 100,
                            'id_product' => '2',
                            'name' => $productName_1,
                            'rate' => 21,
                            'id_product_attribute' => '9',
                            'id_customization' => NULL,
                        ],
                ],
                'psGiftWrapping' => '1',
                'selectedVoucherCategory' => 'meal',
                'translationMocks' => [
                    0 => [
                        'function' => 'lang',
                        'expects' => $shipping,
                        'return' => $shipping,
                        'at' => 0
                    ]
                ],
                'toolsMocks' => [
                    0 => [
                        'function' => 'strtoupper',
                        'expects' => $currencyIsoCode,
                        'return' => $currencyIsoCode,
                        'at' => 0
                    ]
                ],
                'mocks' => [],
                'result' => [
                    0 =>
                        (new Line())
                            ->setName($productName_1)
                            ->setQuantity(1)
                            ->setSku('2¤9¤0')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount($currencyIsoCode, '100.00'))
                            ->setTotalPrice(new Amount($currencyIsoCode, '100.00'))
                            ->setVatAmount(new Amount($currencyIsoCode, '17.36'))
                            ->setCategory('meal')
                            ->setVatRate('21.00'),
                    1 =>
                        (new Line())
                            ->setName('Shipping')
                            ->setQuantity(1)
                            ->setSku('')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount($currencyIsoCode, '4.84'))
                            ->setTotalPrice(new Amount($currencyIsoCode, '4.84'))
                            ->setVatAmount(new Amount($currencyIsoCode, '0.84'))
                            ->setCategory(null)
                            ->setVatRate('21.00'),
                ]
            ],
            'three products with meal, eco and null category' => [
                'amount' => 138.59,
                'paymentFee' => false,
                'currencyIsoCode' => $currencyIsoCode,
                'cartSummary' => [

                    'gift_products' =>
                        [
                        ],
                    'discounts' =>
                        [
                        ],
                    'total_wrapping' => 10.6,
                    'total_wrapping_tax_exc' => 10.0,
                    'total_shipping' => 4.84,
                    'total_shipping_tax_exc' => 4,
                    'total_products_wt' => 100,
                    'total_products' => 123.15,
                    'total_price' => 138.59,
                    'free_ship' => false,
                ],
                4.84,
                'cartItems' => [
                    0 =>
                        [
                            'total_wt' => 100,
                            'cart_quantity' => '1',
                            'price_wt' => 100,
                            'id_product' => '2',
                            'name' => $productName_1,
                            'rate' => 21,
                            'id_product_attribute' => '9',
                            'id_customization' => NULL,
                            'features' => [
                                0 => [
                                    'id_feature' => '15',
                                    'id_product' => '2',
                                    'id_feature_value' => '31'
                                ]
                            ]
                        ],
                    1 =>
                        [
                            'total_wt' => 19.12,
                            'cart_quantity' => '1',
                            'price_wt' => 23.1352,
                            'id_product' => '2',
                            'name' => $productName_2,
                            'rate' => 21,
                            'id_product_attribute' => '1',
                            'id_customization' => NULL,
                            'features' => [
                                0 => [
                                    'id_feature' => '15',
                                    'id_product' => '3',
                                    'id_feature_value' => '32'
                                ]
                            ]
                        ],
                    2 =>
                        [
                            'total_wt' => 0.01,
                            'cart_quantity' => '1',
                            'price_wt' => 0.0121,
                            'id_product' => '2',
                            'name' => $productName_3,
                            'rate' => 21,
                            'id_product_attribute' => '13',
                            'id_customization' => NULL,
                            'features' => []
                        ],
                ],
                'psGiftWrapping' => '1',
                'selectedVoucherCategory' => 'null',
                'translationMocks' => [
                    0 => [
                        'function' => 'lang',
                        'expects' => $shipping,
                        'return' => $shipping,
                        'at' => 0
                    ],
                    1 => [
                        'function' => 'lang',
                        'expects' => $giftWrapping,
                        'return' => $giftWrapping,
                        'at' => 1
                    ],
                ],
                'toolsMocks' => [
                    0 => [
                        'function' => 'strtoupper',
                        'expects' => $currencyIsoCode,
                        'return' => $currencyIsoCode,
                        'at' => 0
                    ]
                ],
                'mocks' => [
                    0 => [
                        'function' => 'get',
                        'expects' => Config::MOLLIE_VOUCHER_FEATURE_ID,
                        'return' => '15',
                        'at' => 0
                    ],
                    1 => [
                        'function' => 'get',
                        'expects' => Config::MOLLIE_VOUCHER_FEATURE . Config::MOLLIE_VOUCHER_CATEGORY_MEAL,
                        'return' => '31',
                        'at' => 1
                    ],
                    2 => [
                        'function' => 'get',
                        'expects' => Config::MOLLIE_VOUCHER_FEATURE_ID,
                        'return' => '15',
                        'at' => 2
                    ],
                    3 => [
                        'function' => 'get',
                        'expects' => Config::MOLLIE_VOUCHER_FEATURE . Config::MOLLIE_VOUCHER_CATEGORY_MEAL,
                        'return' => '31',
                        'at' => 3
                    ],
                    4 => [
                        'function' => 'get',
                        'expects' => Config::MOLLIE_VOUCHER_FEATURE . Config::MOLLIE_VOUCHER_CATEGORY_GIFT,
                        'return' => '32',
                        'at' => 4
                    ],
                ],
                'result' => [
                    0 =>
                        (new Line())
                            ->setName($productName_1)
                            ->setQuantity(1)
                            ->setSku('2¤9¤0')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount($currencyIsoCode, '100.00'))
                            ->setTotalPrice(new Amount($currencyIsoCode, '100.00'))
                            ->setVatAmount(new Amount($currencyIsoCode, '17.36'))
                            ->setCategory('meal')
                            ->setVatRate('21.00'),
                    1 =>
                        (new Line())
                            ->setName($productName_2)
                            ->setQuantity(1)
                            ->setSku('2¤1¤0')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount($currencyIsoCode, '19.12'))
                            ->setTotalPrice(new Amount($currencyIsoCode, '19.12'))
                            ->setVatAmount(new Amount($currencyIsoCode, '3.32'))
                            ->setCategory('gift')
                            ->setVatRate('21.00'),
                    2 =>
                        (new Line())
                            ->setName($productName_3)
                            ->setQuantity(1)
                            ->setSku('2¤13¤0')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount($currencyIsoCode, '4.03'))
                            ->setTotalPrice(new Amount($currencyIsoCode, '4.03'))
                            ->setVatAmount(new Amount($currencyIsoCode, '0.70'))
                            ->setCategory('')
                            ->setVatRate('21.00'),
                    3 =>
                        (new Line())
                            ->setName($shipping)
                            ->setQuantity(1)
                            ->setSku('')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount($currencyIsoCode, '4.84'))
                            ->setTotalPrice(new Amount($currencyIsoCode, '4.84'))
                            ->setVatAmount(new Amount($currencyIsoCode, '0.84'))
                            ->setCategory(null)
                            ->setVatRate('21.00'),
                    4 =>
                        (new Line())
                            ->setName($giftWrapping)
                            ->setQuantity(1)
                            ->setSku('')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount($currencyIsoCode, '10.60'))
                            ->setTotalPrice(new Amount($currencyIsoCode, '10.60'))
                            ->setVatAmount(new Amount($currencyIsoCode, '0.60'))
                            ->setCategory(null)
                            ->setVatRate('6.00'),
                ]
            ]
        ];
    }
}
