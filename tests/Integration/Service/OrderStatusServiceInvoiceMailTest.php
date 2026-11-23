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
use Mollie\Tests\Integration\Factory\CarrierFactory;
use Mollie\Tests\Integration\Factory\CartFactory;
use Mollie\Tests\Integration\Factory\ProductFactory;

/**
 * PIPRES-891: the order confirmation mail sent on the paid status must see the invoice
 * that PrestaShop creates in the same status change, or the invoice PDF is not attached.
 */
class OrderStatusServiceInvoiceMailTest extends BaseTestCase
{
    const CONFIG_KEYS = ['PS_INVOICE', Config::MOLLIE_SEND_ORDER_CONFIRMATION, 'PS_STOCK_MANAGEMENT'];

    /** @var array */
    private $configBackup = [];

    /** @var array */
    private $contextBackup = [];

    protected function setUp()
    {
        parent::setUp();

        foreach (self::CONFIG_KEYS as $key) {
            $this->configBackup[$key] = \Configuration::hasKey($key) ? \Configuration::get($key) : null;
        }

        \Configuration::updateValue('PS_INVOICE', 1);
        \Configuration::updateValue(Config::MOLLIE_SEND_ORDER_CONFIRMATION, Config::NEW_ORDER_MAIL_SEND_ON_PAID);
        \Configuration::updateValue('PS_STOCK_MANAGEMENT', 1);

        $context = \Context::getContext();
        $this->contextBackup = [
            'controller' => $context->controller ?? null,
            'cart' => $context->cart ?? null,
        ];
        $this->giveContextAFrontControllerContainer();
    }

    protected function tearDown()
    {
        foreach ($this->configBackup as $key => $value) {
            if (null === $value) {
                \Configuration::deleteByName($key);
                continue;
            }

            \Configuration::updateValue($key, $value);
        }

        $context = \Context::getContext();
        $context->controller = $this->contextBackup['controller'];
        $context->cart = $this->contextBackup['cart'];

        parent::tearDown();
    }

    public function testConfirmationMailGetsTheOrderWithItsNewInvoice()
    {
        $orderId = $this->createAwaitingOrder([$this->createProduct()]);

        $mailedOrders = $this->setPaidAndCaptureMailedOrders($orderId, 1);

        $storedInvoiceNumber = (int) (new \Order($orderId))->invoice_number;

        $this->assertGreaterThan(0, $storedInvoiceNumber, 'The paid status should create an invoice.');
        $this->assertSame(
            $storedInvoiceNumber,
            (int) $mailedOrders[0]->invoice_number,
            'The confirmation mail must be built from the order that holds the new invoice.'
        );
    }

    public function testEachSplitOrderConfirmationMailGetsItsOwnInvoice()
    {
        $firstProduct = $this->createProduct();
        $secondProduct = $this->createProduct();
        $this->restrictProductToNewCarrier($firstProduct);
        $this->restrictProductToNewCarrier($secondProduct);

        $orderId = $this->createAwaitingOrder([$firstProduct, $secondProduct]);
        $cartOrders = \Order::getByReference((new \Order($orderId))->reference);
        $this->assertCount(2, $cartOrders, 'The cart should be split into two orders.');

        $mailedOrders = $this->setPaidAndCaptureMailedOrders($orderId, 2);

        $this->assertCount(2, array_unique(array_column($mailedOrders, 'id')), 'Each split order must get its own mail.');

        foreach ($mailedOrders as $mailedOrder) {
            $storedInvoiceNumber = (int) (new \Order((int) $mailedOrder->id))->invoice_number;

            $this->assertGreaterThan(0, $storedInvoiceNumber, 'Every split order should get an invoice.');
            $this->assertSame(
                $storedInvoiceNumber,
                (int) $mailedOrder->invoice_number,
                'Each split order mail must be built from the order that holds its new invoice.'
            );
        }
    }

    /**
     * @return \Order[] copies of the orders as they were handed to the confirmation mail
     */
    private function setPaidAndCaptureMailedOrders(int $orderId, int $expectedMails): array
    {
        $mailedOrders = [];
        $mailService = $this->createMock(MailService::class);
        $mailService->expects($this->exactly($expectedMails))
            ->method('sendOrderConfMail')
            ->willReturnCallback(function (\Order $order) use (&$mailedOrders) {
                $mailedOrders[] = clone $order;
            });

        $orderStatusService = new OrderStatusService($mailService, $this->getService(OrderRepository::class));
        $orderStatusService->setOrderStatus($orderId, PaymentStatus::STATUS_PAID);

        return $mailedOrders;
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

    private function createProduct(): \Product
    {
        /** @var \Product $product */
        $product = ProductFactory::initialize()->create();

        return $product;
    }

    /**
     * PrestaShop splits a cart into one order per package; products that share no carrier end up in different packages.
     */
    private function restrictProductToNewCarrier(\Product $product)
    {
        $carrier = CarrierFactory::create();

        $product->setCarriers([(int) $carrier->id]);
    }

    /**
     * @param \Product[] $products
     */
    private function createAwaitingOrder(array $products): int
    {
        /** @var \Cart $cart */
        $cart = CartFactory::initialize()->create();
        foreach ($products as $product) {
            $cart->updateQty(1, (int) $product->id, $product->getDefaultIdProductAttribute());
        }

        $deliveryOptions = $cart->getDeliveryOptionList();
        $addressId = (int) $cart->id_address_delivery;
        if (!empty($deliveryOptions[$addressId])) {
            $cart->setDeliveryOption([$addressId => (string) key($deliveryOptions[$addressId])]);
        }
        $cart->update();

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
