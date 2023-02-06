<?php

declare(strict_types=1);

namespace Mollie\Subscription\Logger;

use Combination;
use Mollie\Adapter\Language;
use Mollie\Adapter\Link;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;
use Mollie\Subscription\Repository\RecurringOrdersProductRepositoryInterface;
use Product;

class RecurringOrdersPresenter
{
    /** @var RecurringOrderRepositoryInterface */
    private $recurringOrderRepository;
    /** @var Link */
    private $link;
    /** @var RecurringOrdersProductRepositoryInterface */
    private $recurringOrdersProductRepository;
    /** @var Language */
    private $language;

    public function __construct(
        RecurringOrderRepositoryInterface $recurringOrderRepository,
        RecurringOrdersProductRepositoryInterface $recurringOrdersProductRepository,
        Link $link,
        Language $language
    ) {
        $this->recurringOrderRepository = $recurringOrderRepository;
        $this->link = $link;
        $this->recurringOrdersProductRepository = $recurringOrdersProductRepository;
        $this->language = $language;
    }

    public function present(string $molCustomerId): array
    {
        $recurringOrders = $this->recurringOrderRepository->findAllBy(['mollie_customer_id' => $molCustomerId]);

        $recurringOrdersPresentData = [];
        foreach ($recurringOrders as $recurringOrder) {
            $recurringProduct = $this->recurringOrdersProductRepository->findOneBy(['id_mol_recurring_orders_product' => $recurringOrder->id]);
            $product = new Product($recurringProduct->id_product, false, $this->language->getDefaultLanguageId());
            $combination = new Combination($recurringProduct->id_product_attribute, false, $this->language->getDefaultLanguageId());
            $recurringOrderData = [];
            $recurringOrderData['recurring_order'] = $recurringOrder;
            $recurringOrderData['details_url'] = $this->link->getModuleLink('mollie', 'recurringOrderDetail', ['id_mol_recurring_order' => $recurringOrder->id]);
            $recurringOrderData['recurring_product'] = $recurringProduct;
            $recurringOrderData['product'] = $product;
            $recurringOrderData['product_combination_price'] = $product->getPrice(true, $combination->id);
            $recurringOrdersPresentData[] = $recurringOrderData;
        }

        return $recurringOrdersPresentData;
    }
}
