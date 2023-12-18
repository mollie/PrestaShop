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
use Mollie\Adapter\Language;
use Mollie\Exception\MollieException;
use Mollie\Subscription\Api\MethodApi;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;
use Mollie\Subscription\Repository\RecurringOrdersProductRepositoryInterface;
use Order;
use PrestaShop\PrestaShop\Adapter\Presenter\Order\OrderPresenter;
use Product;

if (!defined('_PS_VERSION_')) {
    exit;
}

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
    /** @var OrderDetailPresenter */
    private $orderDetailPresenter;

    public function __construct(
        RecurringOrderRepositoryInterface $recurringOrderRepository,
        RecurringOrdersProductRepositoryInterface $recurringOrdersProductRepository,
        Language $language,
        MethodApi $methodApi,
        OrderDetailPresenter $orderDetailPresenter
    ) {
        $this->recurringOrderRepository = $recurringOrderRepository;
        $this->recurringOrdersProductRepository = $recurringOrdersProductRepository;
        $this->language = $language;
        $this->methodApi = $methodApi;
        $this->orderDetailPresenter = $orderDetailPresenter;
    }

    /**
     * @throws \Throwable
     */
    public function present(int $recurringOrderId): array
    {
        try {
            /** @var \MolRecurringOrder $recurringOrder */
            $recurringOrder = $this->recurringOrderRepository->findOrFail([
                'id_mol_recurring_order' => $recurringOrderId,
            ]);
        } catch (\Throwable $exception) {
            throw MollieException::unknownError($exception);
        }

        try {
            /** @var \MolRecurringOrdersProduct $recurringProduct */
            $recurringProduct = $this->recurringOrdersProductRepository->findOrFail([
                'id_mol_recurring_orders_product' => $recurringOrder->id_mol_recurring_orders_product,
            ]);
        } catch (\Throwable $exception) {
            throw MollieException::unknownError($exception);
        }

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
        $recurringOrderData['order'] = (new OrderPresenter())->present($order);
        $recurringOrderData['order_detail'] = $this->orderDetailPresenter->present(
            $recurringOrder,
            $recurringProduct
        );
        $recurringOrderData['payment_methods'] = $this->methodApi->getMethodsForFirstPayment($this->language->getContextLanguage()->locale, $currency->iso_code);

        return $recurringOrderData;
    }
}
