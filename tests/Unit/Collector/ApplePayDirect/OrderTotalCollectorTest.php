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

namespace Mollie\Tests\Unit\Collector\ApplePayDirect;

use Cart;
use Mollie\Builder\ApplePayDirect\ApplePayLineItemsBuilder;
use Mollie\Collector\ApplePayDirect\OrderTotalCollector;
use Mollie\DTO\ApplePay\Carrier\Carrier as AppleCarrier;
use Mollie\DTO\PaymentFeeData;
use Mollie\Service\OrderPaymentFeeService;
use PHPUnit\Framework\TestCase;

class OrderTotalCollectorTest extends TestCase
{
    /**
     * @dataProvider  getCarriersDataProvider
     */
    public function testGetOrderTotals($carriers, $expectedResult): void
    {
        $paymentFeeData = $this->createMock(PaymentFeeData::class);
        $paymentFeeData->method('getPaymentFeeTaxIncl')->willReturn(0.5);

        $orderPaymentFeeService = $this->createMock(OrderPaymentFeeService::class);
        $orderPaymentFeeService->method('getPaymentFee')->willReturn($paymentFeeData);

        $cart = $this->createMock(Cart::class);
        $cart->method('getOrderTotal')->willReturn(1.95);

        $lineItems = [['type' => 'final', 'label' => 'VAT', 'amount' => '0.33']];

        $lineItemsBuilder = $this->createMock(ApplePayLineItemsBuilder::class);
        $lineItemsBuilder->method('build')->with($cart, 1.95, 0.5)->willReturn($lineItems);

        $orderTotalCollector = new OrderTotalCollector($orderPaymentFeeService, $lineItemsBuilder);
        $orderTotals = $orderTotalCollector->getOrderTotals($carriers, $cart);

        $this->assertEquals($expectedResult, $orderTotals);
    }

    public function getCarriersDataProvider(): array
    {
        return [
            'basic case' => [
                'carriers' => [
                    new AppleCarrier('testName', 'test delay', 1, 0.54),
                ],
                'expectedResult' => [
                    [
                        'type' => 'final',
                        'label' => 'testName',
                        'amount' => 2.45,
                        'amountWithoutFee' => 1.95,
                        'lineItems' => [['type' => 'final', 'label' => 'VAT', 'amount' => '0.33']],
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
                        'type' => 'final',
                        'label' => 'testName1',
                        'amount' => 2.45,
                        'amountWithoutFee' => 1.95,
                        'lineItems' => [['type' => 'final', 'label' => 'VAT', 'amount' => '0.33']],
                    ],
                    [
                        'type' => 'final',
                        'label' => 'testName2',
                        'amount' => 2.45,
                        'amountWithoutFee' => 1.95,
                        'lineItems' => [['type' => 'final', 'label' => 'VAT', 'amount' => '0.33']],
                    ],
                ],
            ],
        ];
    }
}
