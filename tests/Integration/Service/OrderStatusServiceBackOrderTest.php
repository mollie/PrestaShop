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

namespace Mollie\Tests\Integration\Service;

use Mollie\Api\Types\PaymentStatus;
use Mollie\Config\Config;
use Mollie\Service\OrderStatusService;
use Mollie\Tests\Integration\BaseTestCase;
use Mollie\Tests\Integration\Factory\CartFactory;
use Mollie\Tests\Integration\Factory\ProductFactory;

/**
 * PIPRES-779: an order placed when a product is at exactly 0 stock (backorders allowed)
 * must be moved to "On backorder (paid)" after payment, not left on the plain paid status.
 *
 * These tests build a real cart + product + order and run the actual OrderStatusService
 * against them, so they exercise the OrderDetail stock columns as PrestaShop persists them.
 */
class OrderStatusServiceBackOrderTest extends BaseTestCase
{
    protected function setUp()
    {
        parent::setUp();

        \Configuration::updateValue('PS_STOCK_MANAGEMENT', 1);
        \Configuration::updateValue('PS_ORDER_OUT_OF_STOCK', 1);
    }

    public function testOrderPlacedAtZeroStockBecomesBackOrderPaid()
    {
        $orderId = $this->createPaidOrderForStock(0);
        $order = new \Order($orderId);

        $this->assertSame(
            (int) \Configuration::get('PS_OS_OUTOFSTOCK_PAID'),
            (int) $order->current_state,
            'An order for a product at 0 stock should land on "On backorder (paid)".'
        );
    }

    public function testInStockOrderStaysOnPaidStatus()
    {
        $orderId = $this->createPaidOrderForStock(10);
        $order = new \Order($orderId);

        $this->assertSame(
            (int) \Configuration::get(Config::MOLLIE_STATUS_PAID),
            (int) $order->current_state,
            'An in-stock order must stay on the paid status, never the backorder status.'
        );
    }

    /**
     * Create an order for one unit of a product whose available stock is $stock,
     * then move it to paid through the real OrderStatusService.
     */
    private function createPaidOrderForStock(int $stock): int
    {
        /** @var \Product $product */
        $product = ProductFactory::initialize()->create();
        \StockAvailable::setQuantity((int) $product->id, 0, $stock);

        /** @var \Cart $cart */
        $cart = CartFactory::initialize()->create();
        $cart->updateQty(1, (int) $product->id, $product->getDefaultIdProductAttribute());
        $cart->update();

        \Context::getContext()->cart = $cart;

        /** @var \Mollie $module */
        $module = \Module::getInstanceByName('mollie');
        $module->validateOrder(
            (int) $cart->id,
            (int) \Configuration::get(Config::MOLLIE_STATUS_AWAITING),
            (float) $cart->getOrderTotal(true, \Cart::BOTH),
            'PIPRES-779 test',
            null,
            [],
            null,
            false,
            false
        );

        $orderId = (int) \Order::getIdByCartId((int) $cart->id);

        /** @var OrderStatusService $orderStatusService */
        $orderStatusService = $this->getService(OrderStatusService::class);
        $orderStatusService->setOrderStatus($orderId, PaymentStatus::STATUS_PAID);

        return $orderId;
    }
}
