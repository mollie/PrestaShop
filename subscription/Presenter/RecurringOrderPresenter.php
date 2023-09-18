<?php

declare(strict_types=1);

namespace Mollie\Subscription\Presenter;

use Currency;
use Mollie\Adapter\Language;
use Mollie\Subscription\Api\MethodApi;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;
use Mollie\Subscription\Repository\RecurringOrdersProductRepositoryInterface;
use Order;
use Product;

class RecurringOrderPresenter
{
    /** @var RecurringOrderRepositoryInterface */
    private $recurringOrderRepository;
    /** @var RecurringOrdersProductRepositoryInterface */
    private $recurringOrdersProductRepository;
    /** @var Language */
    private $language;
    /** @var MethodApi */
    private $methodApi;
    /** @var OrderPresenter */
    private $orderPresenter;

    public function __construct(
        RecurringOrderRepositoryInterface $recurringOrderRepository,
        RecurringOrdersProductRepositoryInterface $recurringOrdersProductRepository,
        Language $language,
        MethodApi $methodApi,
        OrderPresenter $orderPresenter
    ) {
        $this->recurringOrderRepository = $recurringOrderRepository;
        $this->recurringOrdersProductRepository = $recurringOrdersProductRepository;
        $this->language = $language;
        $this->methodApi = $methodApi;
        $this->orderPresenter = $orderPresenter;
    }

    public function present(int $recurringOrderId): array
    {
        $recurringOrder = $this->recurringOrderRepository->findOneBy(['id_mol_recurring_order' => $recurringOrderId]);
        $recurringProduct = $this->recurringOrdersProductRepository->findOneBy(['id_mol_recurring_orders_product' => $recurringOrderId]);

        $product = new Product($recurringProduct->id_product, false, $this->language->getDefaultLanguageId());
        $order = new Order($recurringOrder->id_order);

        /*
         * NOTE: setting address IDs only for presentation, don't want to edit original recurring order.
         */
        $order->id_address_delivery = $recurringOrder->id_address_delivery;
        $order->id_address_invoice = $recurringOrder->id_address_invoice;

        $currency = new Currency($order->id_currency);

        $recurringOrderData = [];
        $recurringOrderData['recurring_order'] = $recurringOrder;
        $recurringOrderData['recurring_product'] = $recurringProduct;
        $recurringOrderData['product'] = $product;
        $recurringOrderData['order'] = $this->orderPresenter->present(
            $order,
            (int) $recurringProduct->id_product_attribute,
            (float) $recurringOrder->total_tax_incl
        );
        $recurringOrderData['payment_methods'] = $this->methodApi->getMethodsForFirstPayment($this->language->getContextLanguage()->locale, $currency->iso_code);

        return $recurringOrderData;
    }
}
