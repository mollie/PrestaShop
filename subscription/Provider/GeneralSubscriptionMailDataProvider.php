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

namespace Mollie\Subscription\Provider;

use Mollie\Adapter\Context;
use Mollie\Repository\CustomerRepositoryInterface;
use Mollie\Repository\ProductRepositoryInterface;
use Mollie\Shared\Infrastructure\Repository\CurrencyRepositoryInterface;
use Mollie\Subscription\DTO\Mail\GeneralSubscriptionMailData;
use Mollie\Subscription\Exception\CouldNotProvideGeneralSubscriptionMailData;
use Mollie\Subscription\Exception\MollieSubscriptionException;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;
use Mollie\Subscription\Repository\RecurringOrdersProductRepositoryInterface;
use Mollie\Utility\NumberUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class GeneralSubscriptionMailDataProvider
{
    /** @var RecurringOrderRepositoryInterface */
    private $recurringOrderRepository;
    /** @var RecurringOrdersProductRepositoryInterface */
    private $recurringOrdersProductRepository;
    /** @var CustomerRepositoryInterface */
    private $customerRepository;
    /** @var ProductRepositoryInterface */
    private $productRepository;
    /** @var Context */
    private $context;
    /** @var CurrencyRepositoryInterface */
    private $currencyRepository;

    public function __construct(
        RecurringOrderRepositoryInterface $recurringOrderRepository,
        RecurringOrdersProductRepositoryInterface $recurringOrdersProductRepository,
        CustomerRepositoryInterface $customerRepository,
        ProductRepositoryInterface $productRepository,
        Context $context,
        CurrencyRepositoryInterface $currencyRepository
    ) {
        $this->recurringOrderRepository = $recurringOrderRepository;
        $this->recurringOrdersProductRepository = $recurringOrdersProductRepository;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->context = $context;
        $this->currencyRepository = $currencyRepository;
    }

    /**
     * @throws MollieSubscriptionException
     */
    public function run(int $recurringOrderId): GeneralSubscriptionMailData
    {
        // TODO test this with feature test

        /** @var ?\MolRecurringOrder $recurringOrder */
        $recurringOrder = $this->recurringOrderRepository->findOneBy([
            'id_mol_recurring_order' => $recurringOrderId,
        ]);

        if (!$recurringOrder) {
            throw CouldNotProvideGeneralSubscriptionMailData::failedToFindRecurringOrder($recurringOrderId);
        }

        /** @var ?\MolRecurringOrdersProduct $recurringOrderProduct */
        $recurringOrderProduct = $this->recurringOrdersProductRepository->findOneBy([
            'id_mol_recurring_orders_product' => $recurringOrder->id_mol_recurring_orders_product,
        ]);

        if (!$recurringOrderProduct) {
            throw CouldNotProvideGeneralSubscriptionMailData::failedToFindRecurringOrderProduct($recurringOrderId, (int) $recurringOrder->id_mol_recurring_orders_product);
        }

        /** @var ?\Customer $customer */
        $customer = $this->customerRepository->findOneBy([
            'id_customer' => $recurringOrder->id_customer,
        ]);

        if (!$customer) {
            throw CouldNotProvideGeneralSubscriptionMailData::failedToFindCustomer($recurringOrderId, (int) $recurringOrder->id_customer);
        }

        /** @var ?\Product $product */
        $product = $this->productRepository->findOneBy([
            'id_product' => $recurringOrderProduct->id_product,
        ]);

        if (!$product) {
            throw CouldNotProvideGeneralSubscriptionMailData::failedToFindProduct((int) $recurringOrder->id_mol_recurring_orders_product, (int) $recurringOrderProduct->id_product);
        }

        $productName = is_array($product->name) ? ($product->name[$customer->id_lang] ?? '') : $product->name;

        /** @var ?\Currency $currency */
        $currency = $this->currencyRepository->findOneBy([
            'id_currency' => $recurringOrder->id_currency,
        ]);

        if (!$currency) {
            throw CouldNotProvideGeneralSubscriptionMailData::failedToFindCurrency($recurringOrderId, (int) $recurringOrder->id_currency);
        }

        $unitPriceTaxExcl = (float) $this->context->formatPrice(
            NumberUtility::toPrecision(
                (float) $recurringOrderProduct->unit_price,
                NumberUtility::DECIMAL_PRECISION
            ),
            (string) $currency->iso_code
        );

        $totalPriceTaxIncl = (float) $this->context->formatPrice(
            NumberUtility::toPrecision(
                (float) $recurringOrder->total_tax_incl,
                NumberUtility::DECIMAL_PRECISION
            ),
            (string) $currency->iso_code
        );

        return GeneralSubscriptionMailData::create(
            (string) $recurringOrder->mollie_subscription_id,
            (string) $productName,
            $unitPriceTaxExcl,
            (int) $recurringOrderProduct->quantity,
            $totalPriceTaxIncl,
            (string) $customer->firstname,
            (string) $customer->lastname,
            (string) $customer->email,
            (int) $customer->id_lang,
            (int) $customer->id_shop
        );
    }
}
