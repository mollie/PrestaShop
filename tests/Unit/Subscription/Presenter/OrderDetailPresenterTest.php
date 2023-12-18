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

namespace Mollie\Tests\Unit\Subscription\Presenter;

use Mollie\Adapter\Context;
use Mollie\Api\Types\SubscriptionStatus;
use Mollie\Repository\OrderRepositoryInterface;
use Mollie\Repository\ProductRepositoryInterface;
use Mollie\Shared\Core\Shared\Repository\CurrencyRepositoryInterface;
use Mollie\Subscription\Exception\CouldNotPresentOrderDetail;
use Mollie\Subscription\Exception\ExceptionCode;
use Mollie\Subscription\Presenter\OrderDetailPresenter;
use Mollie\Subscription\Repository\OrderDetailRepositoryInterface;
use PHPUnit\Framework\TestCase;

class OrderDetailPresenterTest extends TestCase
{
    /** @var OrderDetailRepositoryInterface */
    private $orderDetailRepository;
    /** @var Context */
    private $context;
    /** @var OrderRepositoryInterface */
    private $orderRepository;
    /** @var ProductRepositoryInterface */
    private $productRepository;
    /** @var CurrencyRepositoryInterface */
    private $currencyRepository;

    public function setUp(): void
    {
        $this->orderDetailRepository = $this->createMock(OrderDetailRepositoryInterface::class);
        $this->context = $this->createMock(Context::class);
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->currencyRepository = $this->createMock(CurrencyRepositoryInterface::class);

        parent::setUp();
    }

    public function testItSuccessfullyPresentsOrderDetail(): void
    {
        /** @var \Order $order */
        $order = $this->createMock(\Order::class);
        $order->id_lang = 1;

        /** @var \OrderDetail $orderDetail */
        $orderDetail = $this->createMock(\OrderDetail::class);
        $orderDetail->unit_price_tax_incl = 1.11;

        /** @var \Product $product */
        $product = $this->createMock(\Product::class);

        /** @var \Currency $currency */
        $currency = $this->createMock(\Currency::class);
        $currency->iso_code = 'EUR';

        /** @var \MolRecurringOrder $recurringOrder */
        $recurringOrder = $this->createMock(\MolRecurringOrder::class);
        $recurringOrder->total_tax_incl = 1.11;
        $recurringOrder->status = SubscriptionStatus::STATUS_ACTIVE;

        /** @var \MolRecurringOrdersProduct $recurringProduct */
        $recurringProduct = $this->createMock(\MolRecurringOrdersProduct::class);
        $recurringProduct->id_product = 1;
        $recurringProduct->id_product_attribute = 1;

        $this->orderRepository->expects($this->once())->method('findOneBy')->willReturn($order);
        $this->orderDetailRepository->expects($this->once())->method('findOneBy')->willReturn($orderDetail);
        $this->productRepository->expects($this->once())->method('findOneBy')->willReturn($product);
        $this->productRepository->expects($this->once())->method('getCombinationImageById')->willReturn(['id_image' => 1]);
        $this->currencyRepository->expects($this->once())->method('findOneBy')->willReturn($currency);

        $this->context->expects($this->once())->method('getProductLink')->willReturn('test-link');
        $this->context->expects($this->once())->method('getImageLink')->willReturn('test-link');
        $this->context->expects($this->exactly(2))->method('formatPrice')->willReturn('123.33$');

        $orderDetailPresenter = new OrderDetailPresenter(
            $this->orderDetailRepository,
            $this->context,
            $this->orderRepository,
            $this->productRepository,
            $this->currencyRepository
        );

        $result = $orderDetailPresenter->present($recurringOrder, $recurringProduct);

        $this->assertArrayHasKey('next_payment_date', $result);
        $this->assertArrayNotHasKey('cancelled_date', $result);
    }

    public function testItSuccessfullyPresentsCancelledOrderDetailWithDefaultProductImage(): void
    {
        /** @var \Order $order */
        $order = $this->createMock(\Order::class);
        $order->id_lang = 1;

        /** @var \OrderDetail $orderDetail */
        $orderDetail = $this->createMock(\OrderDetail::class);
        $orderDetail->unit_price_tax_incl = 1.11;

        /** @var \Product $product */
        $product = $this->createMock(\Product::class);

        /** @var \Currency $currency */
        $currency = $this->createMock(\Currency::class);
        $currency->iso_code = 'EUR';

        /** @var \MolRecurringOrder $recurringOrder */
        $recurringOrder = $this->createMock(\MolRecurringOrder::class);
        $recurringOrder->total_tax_incl = 1.11;
        $recurringOrder->status = SubscriptionStatus::STATUS_CANCELED;

        /** @var \MolRecurringOrdersProduct $recurringProduct */
        $recurringProduct = $this->createMock(\MolRecurringOrdersProduct::class);
        $recurringProduct->id_product = 1;
        $recurringProduct->id_product_attribute = 1;

        $this->orderRepository->expects($this->once())->method('findOneBy')->willReturn($order);
        $this->orderDetailRepository->expects($this->once())->method('findOneBy')->willReturn($orderDetail);
        $this->productRepository->expects($this->once())->method('findOneBy')->willReturn($product);
        $this->productRepository->expects($this->once())->method('getCombinationImageById')->willReturn(null);
        $this->productRepository->expects($this->once())->method('getCover')->willReturn(['id_image' => 1]);
        $this->currencyRepository->expects($this->once())->method('findOneBy')->willReturn($currency);

        $this->context->expects($this->once())->method('getProductLink')->willReturn('test-link');
        $this->context->expects($this->once())->method('getImageLink')->willReturn('test-link');
        $this->context->expects($this->exactly(2))->method('formatPrice')->willReturn('123.33$');

        $orderDetailPresenter = new OrderDetailPresenter(
            $this->orderDetailRepository,
            $this->context,
            $this->orderRepository,
            $this->productRepository,
            $this->currencyRepository
        );

        $result = $orderDetailPresenter->present($recurringOrder, $recurringProduct);

        $this->assertArrayNotHasKey('next_payment_date', $result);
        $this->assertArrayHasKey('cancelled_date', $result);
    }

    public function testItUnsuccessfullyPresentsOrderDetailMissingOrder(): void
    {
        /** @var \MolRecurringOrder $recurringOrder */
        $recurringOrder = $this->createMock(\MolRecurringOrder::class);
        $recurringOrder->total_tax_incl = 1.11;
        $recurringOrder->status = SubscriptionStatus::STATUS_CANCELED;

        /** @var \MolRecurringOrdersProduct $recurringProduct */
        $recurringProduct = $this->createMock(\MolRecurringOrdersProduct::class);
        $recurringProduct->id_product = 1;
        $recurringProduct->id_product_attribute = 1;

        $this->orderRepository->expects($this->once())->method('findOneBy')->willReturn(null);

        $orderDetailPresenter = new OrderDetailPresenter(
            $this->orderDetailRepository,
            $this->context,
            $this->orderRepository,
            $this->productRepository,
            $this->currencyRepository
        );

        $this->expectException(CouldNotPresentOrderDetail::class);
        $this->expectExceptionCode(ExceptionCode::ORDER_FAILED_TO_FIND_ORDER);

        $orderDetailPresenter->present($recurringOrder, $recurringProduct);
    }

    public function testItUnsuccessfullyPresentsOrderDetailMissingOrderDetail(): void
    {
        /** @var \Order $order */
        $order = $this->createMock(\Order::class);
        $order->id_lang = 1;

        /** @var \MolRecurringOrder $recurringOrder */
        $recurringOrder = $this->createMock(\MolRecurringOrder::class);
        $recurringOrder->total_tax_incl = 1.11;
        $recurringOrder->status = SubscriptionStatus::STATUS_CANCELED;

        /** @var \MolRecurringOrdersProduct $recurringProduct */
        $recurringProduct = $this->createMock(\MolRecurringOrdersProduct::class);
        $recurringProduct->id_product = 1;
        $recurringProduct->id_product_attribute = 1;

        $this->orderRepository->expects($this->once())->method('findOneBy')->willReturn($order);
        $this->orderDetailRepository->expects($this->once())->method('findOneBy')->willReturn(null);

        $orderDetailPresenter = new OrderDetailPresenter(
            $this->orderDetailRepository,
            $this->context,
            $this->orderRepository,
            $this->productRepository,
            $this->currencyRepository
        );

        $this->expectException(CouldNotPresentOrderDetail::class);
        $this->expectExceptionCode(ExceptionCode::ORDER_FAILED_TO_FIND_ORDER_DETAIL);

        $orderDetailPresenter->present($recurringOrder, $recurringProduct);
    }

    public function testItUnsuccessfullyPresentsOrderDetailMissingProduct(): void
    {
        /** @var \Order $order */
        $order = $this->createMock(\Order::class);
        $order->id_lang = 1;

        /** @var \OrderDetail $orderDetail */
        $orderDetail = $this->createMock(\OrderDetail::class);
        $orderDetail->unit_price_tax_incl = 1.11;

        /** @var \MolRecurringOrder $recurringOrder */
        $recurringOrder = $this->createMock(\MolRecurringOrder::class);
        $recurringOrder->total_tax_incl = 1.11;
        $recurringOrder->status = SubscriptionStatus::STATUS_CANCELED;

        /** @var \MolRecurringOrdersProduct $recurringProduct */
        $recurringProduct = $this->createMock(\MolRecurringOrdersProduct::class);
        $recurringProduct->id_product = 1;
        $recurringProduct->id_product_attribute = 1;

        $this->orderRepository->expects($this->once())->method('findOneBy')->willReturn($order);
        $this->orderDetailRepository->expects($this->once())->method('findOneBy')->willReturn($orderDetail);
        $this->productRepository->expects($this->once())->method('findOneBy')->willReturn(null);

        $orderDetailPresenter = new OrderDetailPresenter(
            $this->orderDetailRepository,
            $this->context,
            $this->orderRepository,
            $this->productRepository,
            $this->currencyRepository
        );

        $this->expectException(CouldNotPresentOrderDetail::class);
        $this->expectExceptionCode(ExceptionCode::ORDER_FAILED_TO_FIND_PRODUCT);

        $orderDetailPresenter->present($recurringOrder, $recurringProduct);
    }

    public function testItUnsuccessfullyPresentsOrderDetailMissingCurrency(): void
    {
        /** @var \Order $order */
        $order = $this->createMock(\Order::class);
        $order->id_lang = 1;

        /** @var \OrderDetail $orderDetail */
        $orderDetail = $this->createMock(\OrderDetail::class);
        $orderDetail->unit_price_tax_incl = 1.11;

        /** @var \Product $product */
        $product = $this->createMock(\Product::class);

        /** @var \MolRecurringOrder $recurringOrder */
        $recurringOrder = $this->createMock(\MolRecurringOrder::class);
        $recurringOrder->total_tax_incl = 1.11;
        $recurringOrder->status = SubscriptionStatus::STATUS_CANCELED;

        /** @var \MolRecurringOrdersProduct $recurringProduct */
        $recurringProduct = $this->createMock(\MolRecurringOrdersProduct::class);
        $recurringProduct->id_product = 1;
        $recurringProduct->id_product_attribute = 1;

        $this->orderRepository->expects($this->once())->method('findOneBy')->willReturn($order);
        $this->orderDetailRepository->expects($this->once())->method('findOneBy')->willReturn($orderDetail);
        $this->productRepository->expects($this->once())->method('findOneBy')->willReturn($product);
        $this->currencyRepository->expects($this->once())->method('findOneBy')->willReturn(null);

        $orderDetailPresenter = new OrderDetailPresenter(
            $this->orderDetailRepository,
            $this->context,
            $this->orderRepository,
            $this->productRepository,
            $this->currencyRepository
        );

        $this->expectException(CouldNotPresentOrderDetail::class);
        $this->expectExceptionCode(ExceptionCode::ORDER_FAILED_TO_FIND_CURRENCY);

        $orderDetailPresenter->present($recurringOrder, $recurringProduct);
    }
}
