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

use Mollie\DTO\ApplePay\Carrier\Carrier as AppleCarrier;
use Mollie\Utility\ApplePayDirect\ShippingMethodUtility;
use PHPUnit\Framework\TestCase;

class ShippingMethodUtilityTest extends TestCase
{
    /**
     * @dataProvider getCarriersDataProvider
     */
    public function testCollectShippingMethodData(array $carriers, array $expectedResult)
    {
        $cart = $this->createMock(Cart::class);
        $cart->method('getOrderTotal')->willReturn(1.95);
        $shippingMethods = ShippingMethodUtility::collectShippingMethodData($carriers, $cart);

        $this->assertEquals($expectedResult, $shippingMethods);
    }

    public function getCarriersDataProvider()
    {
        return [
            'basic case' => [
                'carriers' => [
                    new AppleCarrier('testName', 'test delay', 1, 0.54),
                ],
                'expectedResult' => [
                    [
                        'identifier' => 1,
                        'label' => 'testName',
                        'amount' => 1.95,
                        'detail' => 'test delay',
                    ],
                ],
            ],
            'no carriers' => [
                'carriers' => [],
                'expectedResult' => [],
            ],
            'multiple carriers' => [
                'carriers' => [
                    new AppleCarrier('testName1', 'test delay1', 1, 0.54),
                    new AppleCarrier('testName2', 'test delay2', 2, 0),
                ],
                'expectedResult' => [
                    [
                        'identifier' => 1,
                        'label' => 'testName1',
                        'amount' => 1.95,
                        'detail' => 'test delay1',
                    ],
                    [
                        'identifier' => 2,
                        'label' => 'testName2',
                        'amount' => 1.95,
                        'detail' => 'test delay2',
                    ],
                ],
            ],
        ];
    }
}
