<?php

declare(strict_types=1);

namespace Mollie\Subscription\Logger;

use Combination;
use Currency;
use Mollie\Adapter\Language;
use Mollie\Subscription\Api\MethodApi;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;
use Mollie\Subscription\Repository\RecurringOrdersProductRepositoryInterface;
use Order;
use PrestaShop\PrestaShop\Adapter\Presenter\Order\OrderPresenter;
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

    public function __construct(
        RecurringOrderRepositoryInterface $recurringOrderRepository,
        RecurringOrdersProductRepositoryInterface $recurringOrdersProductRepository,
        Language $language,
        MethodApi $methodApi
    ) {
        $this->recurringOrderRepository = $recurringOrderRepository;
        $this->recurringOrdersProductRepository = $recurringOrdersProductRepository;
        $this->language = $language;
        $this->methodApi = $methodApi;
    }

    public function present(int $recurringOrderId): array
    {
        $recurringOrder = $this->recurringOrderRepository->findOneBy(['id_mol_recurring_order' => $recurringOrderId]);
        $recurringProduct = $this->recurringOrdersProductRepository->findOneBy(['id_mol_recurring_orders_product' => $recurringOrderId]);

        $product = new Product($recurringProduct->id_product, null, $this->language->getDefaultLanguageId());
        $combination = new Combination($recurringProduct->id_product_attribute, null, $this->language->getDefaultLanguageId());
        $order = new Order($recurringOrder->id_order);
        $currency = new Currency($order->id_currency);
        $recurringOrderData = [];
        $recurringOrderData['recurring_order'] = $recurringOrder;
        $recurringOrderData['recurring_product'] = $recurringProduct;
        $recurringOrderData['product'] = $product;
        $recurringOrderData['product_combination_price'] = $product->getPrice(true, $combination->id);
        $recurringOrderData['order'] = (new OrderPresenter())->present($order);
        $recurringOrderData['payment_methods'] = $this->methodApi->getMethodsForFirstPayment($this->language->getContextLanguage()->locale, $currency->iso_code);

        return $recurringOrderData;
    }
}
