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

namespace Mollie\Tests\Unit\Service;

use Mollie\Adapter\ToolsAdapter;
use Mollie\Service\MinimumPurchaseService;
use Mollie\Tests\Unit\BaseTestCase;

class MinimumPurchaseServiceTest extends BaseTestCase
{
    /** @var ToolsAdapter|\PHPUnit\Framework\MockObject\MockObject */
    private $tools;
    /** @var string|null */
    private $minimum;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tools = $this->mock(ToolsAdapter::class);
        $this->tools->method('displayPrice')->willReturnCallback(function ($price) {
            return number_format((float) $price, 2, '.', '') . ' EUR';
        });
        $this->configuration->method('get')->willReturnCallback(function ($key) {
            return $key === 'PS_PURCHASE_MINIMUM' ? $this->minimum : null;
        });
        $this->context->method('trans')->willReturnCallback(function ($id, array $parameters, $domain) {
            return $domain . ': ' . strtr($id, $parameters);
        });
        $this->cart->id_currency = (int) \Configuration::get('PS_CURRENCY_DEFAULT');
    }

    private function service(): MinimumPurchaseService
    {
        return new MinimumPurchaseService($this->configuration, $this->tools, $this->context);
    }

    private function cartWithProductsTotal(float $total): void
    {
        $this->cart->method('getOrderTotal')->with(false, \Cart::ONLY_PRODUCTS)->willReturn($total);
    }

    public function testACartIsNeverBelowWhenTheShopSetsNoMinimum(): void
    {
        $this->minimum = '0';
        $this->cartWithProductsTotal(11.9);

        $this->assertFalse($this->service()->isBelowMinimum($this->cart));
    }

    public function testACartIsNeverBelowWhenTheMinimumIsMissing(): void
    {
        $this->minimum = null;
        $this->cartWithProductsTotal(0.0);

        $this->assertFalse($this->service()->isBelowMinimum($this->cart));
    }

    public function testAProductsTotalUnderTheMinimumIsBelow(): void
    {
        $this->minimum = '100';
        $this->cartWithProductsTotal(11.9);

        $this->assertTrue($this->service()->isBelowMinimum($this->cart));
    }

    public function testAProductsTotalEqualToTheMinimumIsNotBelow(): void
    {
        $this->minimum = '100';
        $this->cartWithProductsTotal(100.0);

        $this->assertFalse($this->service()->isBelowMinimum($this->cart));
    }

    public function testTheMessageIsEmptyWhenTheMinimumIsReached(): void
    {
        $this->minimum = '100';
        $this->cartWithProductsTotal(100.0);

        $this->assertSame('', $this->service()->getNotReachedMessage($this->cart));
    }

    public function testTheMessageReusesTheCheckoutStringWithBothAmounts(): void
    {
        $this->minimum = '100';
        $this->cartWithProductsTotal(11.9);

        $this->assertSame(
            'Shop.Theme.Checkout: A minimum shopping cart total of 100.00 EUR (tax excl.) is required to validate your order. Current cart total is 11.90 EUR (tax excl.).',
            $this->service()->getNotReachedMessage($this->cart)
        );
    }
}
