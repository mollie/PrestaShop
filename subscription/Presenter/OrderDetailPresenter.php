<?php

namespace Mollie\Subscription\Presenter;

use Mollie\Adapter\Context;
use Mollie\Api\Types\SubscriptionStatus;
use Mollie\Repository\CurrencyRepositoryInterface;
use Mollie\Repository\OrderRepositoryInterface;
use Mollie\Repository\ProductRepositoryInterface;
use Mollie\Subscription\Exception\CouldNotPresentOrderDetail;
use Mollie\Subscription\Repository\OrderDetailRepositoryInterface;
use Mollie\Utility\NumberUtility;

class OrderDetailPresenter
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

    public function __construct(
        OrderDetailRepositoryInterface $orderDetailRepository,
        Context $context,
        OrderRepositoryInterface $orderRepository,
        ProductRepositoryInterface $productRepository,
        CurrencyRepositoryInterface $currencyRepository
    ) {
        $this->orderDetailRepository = $orderDetailRepository;
        $this->context = $context;
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->currencyRepository = $currencyRepository;
    }

    /**
     * @throws CouldNotPresentOrderDetail
     */
    public function present(
        \MolRecurringOrder $recurringOrder,
        \MolRecurringOrdersProduct $recurringProduct
    ): array {
        $result = [];

        /** @var \Order|null $order */
        $order = $this->orderRepository->findOneBy([
            'id_order' => (int) $recurringOrder->id_order,
        ]);

        if (!$order) {
            throw CouldNotPresentOrderDetail::failedToFindOrder();
        }

        /** @var \OrderDetail|null $orderDetail */
        $orderDetail = $this->orderDetailRepository->findOneBy([
            'id_order' => (int) $recurringOrder->id_order,
            'product_id' => (int) $recurringProduct->id_product,
            'product_attribute_id' => (int) $recurringProduct->id_product_attribute,
        ]);

        if (!$orderDetail) {
            throw CouldNotPresentOrderDetail::failedToFindOrderDetail();
        }

        /** @var \Product|null $product */
        $product = $this->productRepository->findOneBy([
            'id_product' => (int) $recurringProduct->id_product,
        ]);

        if (!$product) {
            throw CouldNotPresentOrderDetail::failedToFindProduct();
        }

        /** @var \Currency|null $currency */
        $currency = $this->currencyRepository->findOneBy([
            'id_currency' => (int) $order->id_currency,
        ]);

        if (!$currency) {
            throw CouldNotPresentOrderDetail::failedToFindCurrency();
        }

        $linkRewrite = is_array($product->link_rewrite) && isset($product->link_rewrite[$order->id_lang])
            ? $product->link_rewrite[$order->id_lang]
            : $product->link_rewrite;

        $image = $this->productRepository->getCombinationImageById((int) $recurringProduct->id_product_attribute, (int) $order->id_lang);

        if (!$image) {
            $image = $this->productRepository->getCover((int) $recurringProduct->id_product);
        }

        $result['name'] = $orderDetail->product_name;
        $result['link'] = $this->context->getProductLink($product);
        $result['img'] = $this->context->getImageLink($linkRewrite, (string) $image['id_image']);
        $result['quantity'] = $orderDetail->product_quantity;
        $result['unit_price'] = $this->context->formatPrice(
            NumberUtility::toPrecision(
                (float) $orderDetail->unit_price_tax_incl,
                NumberUtility::DECIMAL_PRECISION
            ),
            $currency->iso_code
        );
        $result['total'] = $this->context->formatPrice(
            NumberUtility::toPrecision(
                (float) $recurringOrder->total_tax_incl,
                NumberUtility::DECIMAL_PRECISION
            ),
            $currency->iso_code
        );

        $result['status'] = $recurringOrder->status;
        $result['start_date'] = $recurringOrder->date_add;

        if ($recurringOrder->status === SubscriptionStatus::STATUS_ACTIVE) {
            $result['next_payment_date'] = $recurringOrder->next_payment;
        }

        if ($recurringOrder->status === SubscriptionStatus::STATUS_CANCELED) {
            $result['cancelled_date'] = $recurringOrder->cancelled_at;
        }

        return $result;
    }
}
