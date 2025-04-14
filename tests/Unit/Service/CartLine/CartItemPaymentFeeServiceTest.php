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

namespace Unit\Service\CartLine;

use Mollie\DTO\PaymentFeeData;
use mollie\src\Service\CartLine\CartItemPaymentFeeService;
use Mollie\Tests\Unit\BaseTestCase;

class CartItemPaymentFeeServiceTest extends BaseTestCase
{
    /** @dataProvider dataProvider */
    public function testItAddsPaymentFee($orderLines)
    {
        $paymentFeeDTO = new PaymentFeeData(
            10,
            10,
            0,
            true
        );

        $cartItemPaymentFeeService = new CartItemPaymentFeeService($this->languageService);

        $result = $cartItemPaymentFeeService->addPaymentFeeLine($paymentFeeDTO, $orderLines);

        $this->assertNotEmpty($result['surcharge']);
    }

    /** @dataProvider dataProvider */
    public function testItDontAddPaymentFee($orderLines)
    {
        $paymentFeeDTO = new PaymentFeeData(
            10,
            10,
            0,
            false
        );

        $cartItemPaymentFeeService = new CartItemPaymentFeeService($this->languageService);

        $result = $cartItemPaymentFeeService->addPaymentFeeLine($paymentFeeDTO, $orderLines);

        $this->assertSame($result, $orderLines);
    }

    public function dataProvider()
    {
        return [
            'case1' => [
                'orderLines' => [
                    'orderLines' => [
                        'name' => 'product name',
                        'sku' => '123465789',
                        'targetVat' => 21.0,
                        'quantity' => 1,
                        'unitPrice' => 10.0,
                        'totalAmount' => 12.1,
                        'category' => 'products',
                        'product_url' => 'https://www.example.com/product.png',
                        'image_url' => 'https://www.example.com/product.png',
                    ],
                ],
            ],
        ];
    }
}
