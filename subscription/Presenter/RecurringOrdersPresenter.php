<?php

declare(strict_types=1);

namespace Mollie\Subscription\Logger;

use Currency;
use Mollie\Adapter\Language;
use Mollie\Adapter\Link;
use Mollie\Adapter\ToolsAdapter;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;
use Mollie\Subscription\Repository\RecurringOrdersProductRepositoryInterface;
use MolRecurringOrder;
use PrestaShop\Decimal\Number;
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
    /** @var ToolsAdapter */
    private $tools;

    public function __construct(
        RecurringOrderRepositoryInterface $recurringOrderRepository,
        RecurringOrdersProductRepositoryInterface $recurringOrdersProductRepository,
        Link $link,
        Language $language,
        ToolsAdapter $tools
    ) {
        $this->recurringOrderRepository = $recurringOrderRepository;
        $this->link = $link;
        $this->recurringOrdersProductRepository = $recurringOrdersProductRepository;
        $this->language = $language;
        $this->tools = $tools;
    }

    public function present(string $molCustomerId): array
    {
        $recurringOrders = $this->recurringOrderRepository->findAllBy(['mollie_customer_id' => $molCustomerId])->getResults();

        // this part sorts array so that the new ones are at the top
        usort($recurringOrders, function ($a, $b) {
            return $a->date_add < $b->date_add;
        });

        $recurringOrdersPresentData = [];
        /** @var MolRecurringOrder $recurringOrder */
        foreach ($recurringOrders as $recurringOrder) {
            $recurringProduct = $this->recurringOrdersProductRepository->findOneBy(['id_mol_recurring_orders_product' => $recurringOrder->id]);
            $product = new Product($recurringProduct->id_product, false, $this->language->getDefaultLanguageId());
            $totalPrice = (new Number((string) $recurringProduct->unit_price))->times(new Number((string) $recurringProduct->quantity));

            $recurringOrderData = [];
            $recurringOrderData['recurring_order'] = $recurringOrder;
            $recurringOrderData['details_url'] = $this->link->getModuleLink('mollie', 'recurringOrderDetail', ['id_mol_recurring_order' => $recurringOrder->id]);
            $recurringOrderData['product'] = $product;
            $recurringOrderData['total_price'] = $this->tools->displayPrice($totalPrice->toPrecision(2), new Currency($recurringOrder->id_currency));
            $recurringOrderData['currency'] = new \Currency($recurringOrder->id_currency);
            $recurringOrdersPresentData[] = $recurringOrderData;
        }

        return $recurringOrdersPresentData;
    }
}
