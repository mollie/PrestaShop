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
use Mollie\Repository\OrderRepository;
use Mollie\Service\MailService;
use Mollie\Service\OrderStatusService;
use Mollie\Tests\Integration\BaseTestCase;
use Mollie\Tests\Integration\Factory\CartFactory;
use Mollie\Tests\Integration\Factory\ProductFactory;

/**
 * PIPRES-891: the order confirmation mail sent on the paid status must see the invoice
 * that PrestaShop creates in the same status change, or the invoice PDF is not attached.
 */
class OrderStatusServiceInvoiceMailTest extends BaseTestCase
{
    /** @var array */
    private $configBackup = [];

    protected function setUp()
    {
        parent::setUp();

        foreach (['PS_INVOICE', Config::MOLLIE_SEND_ORDER_CONFIRMATION, 'PS_STOCK_MANAGEMENT'] as $key) {
            $this->configBackup[$key] = \Configuration::get($key);
        }

        \Configuration::updateValue('PS_INVOICE', 1);
        \Configuration::updateValue(Config::MOLLIE_SEND_ORDER_CONFIRMATION, Config::NEW_ORDER_MAIL_SEND_ON_PAID);
        \Configuration::updateValue('PS_STOCK_MANAGEMENT', 1);

        $this->giveContextAFrontControllerContainer();
    }

    protected function tearDown()
    {
        foreach ($this->configBackup as $key => $value) {
            \Configuration::updateValue($key, $value);
        }

        parent::tearDown();
    }

    public function testConfirmationMailGetsTheOrderWithItsNewInvoice()
    {
        $orderId = $this->createAwaitingOrder();

        $mailedOrders = [];
        $mailService = $this->createMock(MailService::class);
        $mailService->expects($this->once())
            ->method('sendOrderConfMail')
            ->willReturnCallback(function (\Order $order) use (&$mailedOrders) {
                $mailedOrders[] = clone $order;
            });

        $orderStatusService = new OrderStatusService($mailService, $this->getService(OrderRepository::class));
        $orderStatusService->setOrderStatus($orderId, PaymentStatus::STATUS_PAID);

        $storedInvoiceNumber = (int) (new \Order($orderId))->invoice_number;

        $this->assertGreaterThan(0, $storedInvoiceNumber, 'The paid status should create an invoice.');
        $this->assertSame(
            $storedInvoiceNumber,
            (int) $mailedOrders[0]->invoice_number,
            'The confirmation mail must be built from the order that holds the new invoice.'
        );
    }

    /**
     * Mollie's actionEmailSendBefore hook reads services from the context controller,
     * which a CLI test run does not have.
     */
    private function giveContextAFrontControllerContainer()
    {
        $context = \Context::getContext();
        if (isset($context->controller)) {
            return;
        }

        $controller = new \FrontController();
        $buildContainer = new \ReflectionMethod($controller, 'buildContainer');
        $buildContainer->setAccessible(true);
        $container = new \ReflectionProperty($controller, 'container');
        $container->setAccessible(true);
        $container->setValue($controller, $buildContainer->invoke($controller));

        $context->controller = $controller;
    }

    private function createAwaitingOrder(): int
    {
        /** @var \Product $product */
        $product = ProductFactory::initialize()->create();
        \StockAvailable::setQuantity((int) $product->id, 0, 10);

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
            'PIPRES-891 test',
            null,
            [],
            null,
            false,
            false
        );

        return (int) \Order::getIdByCartId((int) $cart->id);
    }
}
