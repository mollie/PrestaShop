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

declare(strict_types=1);

namespace Mollie\Subscription\Presenter;

use Currency;
use Mollie\Adapter\Context;
use Mollie\Adapter\Language;
use Mollie\Adapter\Link;
use Mollie\Adapter\ToolsAdapter;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;
use Mollie\Subscription\Repository\RecurringOrdersProductRepositoryInterface;
use Mollie\Utility\NumberUtility;
use MolRecurringOrder;
use Product;

if (!defined('_PS_VERSION_')) {
    exit;
}

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
    /** @var Context */
    private $context;

    public function __construct(
        RecurringOrderRepositoryInterface $recurringOrderRepository,
        RecurringOrdersProductRepositoryInterface $recurringOrdersProductRepository,
        Link $link,
        Language $language,
        ToolsAdapter $tools,
        Context $context
    ) {
        $this->recurringOrderRepository = $recurringOrderRepository;
        $this->link = $link;
        $this->recurringOrdersProductRepository = $recurringOrdersProductRepository;
        $this->language = $language;
        $this->tools = $tools;
        $this->context = $context;
    }

    /**
     * @throws \Throwable
     */
    public function present(string $molCustomerId): array
    {
        /** @var ?\PrestaShopCollection $recurringOrders */
        $recurringOrders = $this->recurringOrderRepository->findAllBy([
            'mollie_customer_id' => $molCustomerId,
        ]);

        if (!$recurringOrders) {
            return [];
        }

        $recurringOrders = $recurringOrders->getResults();

        // this part sorts array so that the new ones are at the top
        usort($recurringOrders, function ($a, $b) {
            return strtotime($b->date_add) - strtotime($a->date_add);
        });

        $recurringOrdersPresentData = [];

        /** @var MolRecurringOrder $recurringOrder */
        foreach ($recurringOrders as $recurringOrder) {
            try {
                /** @var \MolRecurringOrdersProduct $recurringProduct */
                $recurringProduct = $this->recurringOrdersProductRepository->findOrFail([
                    'id_mol_recurring_orders_product' => $recurringOrder->id_mol_recurring_orders_product,
                ]);
            } catch (\Throwable $exception) {
                // TODO log not found data

                continue;
            }

            $product = new Product($recurringProduct->id_product, false, $this->language->getDefaultLanguageId());

            $recurringOrderData = [];
            $recurringOrderData['recurring_order'] = $recurringOrder;
            $recurringOrderData['details_url'] = $this->link->getModuleLink('mollie', 'recurringOrderDetail', ['id_mol_recurring_order' => $recurringOrder->id]);
            $recurringOrderData['product_name'] = is_array($product->name) ? $product->name[$this->context->getLanguageId()] : $product->name;
            $recurringOrderData['total_price'] = $this->tools->displayPrice(NumberUtility::toPrecision((float) $recurringOrder->total_tax_incl, 2), new Currency($recurringOrder->id_currency));
            $recurringOrderData['currency'] = new \Currency($recurringOrder->id_currency);
            $recurringOrdersPresentData[] = $recurringOrderData;
        }

        return $recurringOrdersPresentData;
    }
}
