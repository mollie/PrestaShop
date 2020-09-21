<?php

namespace Service;

use Module;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\DTO\Line;
use Mollie\DTO\Object\Amount;
use Mollie\Repository\AttributeRepository;
use Mollie\Service\CartLinesService;
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
        $mocks,
        $result
    ) {
        $mollieModule = Module::getInstanceByName('mollie');
        /** @var MockObject $configurationAdapter */
        $configurationAdapter = $this->getMockBuilder(ConfigurationAdapter::class)->getMock();
        foreach ($mocks as $mock) {
            $configurationAdapter->expects(self::at($mock['at']))->method($mock['function'])->with($mock['expects'])->willReturn($mock['return']);
        }
        $voucherService = new VoucherService(new AttributeRepository(), $configurationAdapter);
        $cartLineService = new CartLinesService($mollieModule, $voucherService);
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

        $this->assertEquals($result, $cartLines);
    }

    public function cartLinesProvider()
    {
        return [
            'one product with default no voucher category' => [
                'amount' => 104.84,
                'paymentFee' => false,
                'currencyId' => 'EUR',
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
                            'name' => 'Hummingbird printed sweater',
                            'rate' => 21,
                            'id_product_attribute' => '9',
                            'id_customization' => NULL,
                            'features' => []
                        ],
                ],
                'psGiftWrapping' => '1',
                'selectedVoucherCategory' => 'null',
                'mocks' => [],
                'result' => [
                    0 =>
                        (new Line())
                            ->setName('Hummingbird printed sweater')
                            ->setQuantity(1)
                            ->setSku('2¤9¤0')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount('EUR', '100.00'))
                            ->setTotalPrice(new Amount('EUR', '100.00'))
                            ->setVatAmount(new Amount('EUR', '17.36'))
                            ->setCategory(null)
                            ->setVatRate('21.00'),
                    1 =>
                        (new Line())
                            ->setName('Shipping')
                            ->setQuantity(1)
                            ->setSku('')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount('EUR', '4.84'))
                            ->setTotalPrice(new Amount('EUR', '4.84'))
                            ->setVatAmount(new Amount('EUR', '0.84'))
                            ->setCategory(null)
                            ->setVatRate('21.00'),
                ]
            ],
            'one product with default meal category' => [
                'amount' => 104.84,
                'paymentFee' => false,
                'currencyId' => 'EUR',
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
                            'name' => 'Hummingbird printed sweater',
                            'rate' => 21,
                            'id_product_attribute' => '9',
                            'id_customization' => NULL,
                        ],
                ],
                'psGiftWrapping' => '1',
                'selectedVoucherCategory' => 'meal',
                'mocks' => [],
                'result' => [
                    0 =>
                        (new Line())
                            ->setName('Hummingbird printed sweater')
                            ->setQuantity(1)
                            ->setSku('2¤9¤0')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount('EUR', '100.00'))
                            ->setTotalPrice(new Amount('EUR', '100.00'))
                            ->setVatAmount(new Amount('EUR', '17.36'))
                            ->setCategory('meal')
                            ->setVatRate('21.00'),
                    1 =>
                        (new Line())
                            ->setName('Shipping')
                            ->setQuantity(1)
                            ->setSku('')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount('EUR', '4.84'))
                            ->setTotalPrice(new Amount('EUR', '4.84'))
                            ->setVatAmount(new Amount('EUR', '0.84'))
                            ->setCategory(null)
                            ->setVatRate('21.00'),
                ]
            ],
            'three products with meal, eco and null category' => [
                'amount' => 138.59,
                'paymentFee' => false,
                'currencyId' => 'EUR',
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
                            'name' => 'Hummingbird printed sweater',
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
                            'name' => 'Hummingbird printed t-shirt',
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
                            'name' => 'The best is yet to come\' Framed poster',
                            'rate' => 21,
                            'id_product_attribute' => '13',
                            'id_customization' => NULL,
                            'features' => []
                        ],
                ],
                'psGiftWrapping' => '1',
                'selectedVoucherCategory' => 'null',
                'mocks' => [
                    0 => [
                        'function' => 'get',
                        'expects' => Config::MOLLIE_VOUCHER_ATTRIBUTE_ID,
                        'return' => '15',
                        'at' => 0
                    ],
                    1 => [
                        'function' => 'get',
                        'expects' => Config::MOLLIE_VOUCHER_ATTRIBUTE . Config::MOLLIE_VOUCHER_CATEGORY_MEAL,
                        'return' => '31',
                        'at' => 1
                    ],
                    2 => [
                        'function' => 'get',
                        'expects' => Config::MOLLIE_VOUCHER_ATTRIBUTE_ID,
                        'return' => '15',
                        'at' => 2
                    ],
                    3 => [
                        'function' => 'get',
                        'expects' => Config::MOLLIE_VOUCHER_ATTRIBUTE . Config::MOLLIE_VOUCHER_CATEGORY_MEAL,
                        'return' => '31',
                        'at' => 3
                    ],
                    4 => [
                        'function' => 'get',
                        'expects' => Config::MOLLIE_VOUCHER_ATTRIBUTE . Config::MOLLIE_VOUCHER_CATEGORY_GIFT,
                        'return' => '32',
                        'at' => 4
                    ],
                ],
                'result' => [
                    0 =>
                        (new Line())
                            ->setName('Hummingbird printed sweater')
                            ->setQuantity(1)
                            ->setSku('2¤9¤0')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount('EUR', '100.00'))
                            ->setTotalPrice(new Amount('EUR', '100.00'))
                            ->setVatAmount(new Amount('EUR', '17.36'))
                            ->setCategory('meal')
                            ->setVatRate('21.00'),
                    1 =>
                        (new Line())
                            ->setName('Hummingbird printed t-shirt')
                            ->setQuantity(1)
                            ->setSku('2¤1¤0')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount('EUR', '19.12'))
                            ->setTotalPrice(new Amount('EUR', '19.12'))
                            ->setVatAmount(new Amount('EUR', '3.32'))
                            ->setCategory('gift')
                            ->setVatRate('21.00'),
                    2 =>
                        (new Line())
                            ->setName('The best is yet to come\' Framed poster')
                            ->setQuantity(1)
                            ->setSku('2¤13¤0')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount('EUR', '4.03'))
                            ->setTotalPrice(new Amount('EUR', '4.03'))
                            ->setVatAmount(new Amount('EUR', '0.70'))
                            ->setCategory('')
                            ->setVatRate('21.00'),
                    3 =>
                        (new Line())
                            ->setName('Shipping')
                            ->setQuantity(1)
                            ->setSku('')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount('EUR', '4.84'))
                            ->setTotalPrice(new Amount('EUR', '4.84'))
                            ->setVatAmount(new Amount('EUR', '0.84'))
                            ->setCategory(null)
                            ->setVatRate('21.00'),
                    4 =>
                        (new Line())
                            ->setName('Gift wrapping')
                            ->setQuantity(1)
                            ->setSku('')
                            ->setDiscountAmount(null)
                            ->setUnitPrice(new Amount('EUR', '10.60'))
                            ->setTotalPrice(new Amount('EUR', '10.60'))
                            ->setVatAmount(new Amount('EUR', '0.60'))
                            ->setCategory(null)
                            ->setVatRate('6.00'),
                ]
            ]
        ];
    }
}
