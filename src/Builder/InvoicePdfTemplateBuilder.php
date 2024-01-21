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

namespace Mollie\Builder;

use Currency;
use Mollie\Repository\CurrencyRepositoryInterface;
use Mollie\Repository\MolOrderPaymentFeeRepositoryInterface;
use MolOrderPaymentFee;
use Order;
use PrestaShop\PrestaShop\Core\Localization\Locale;

if (!defined('_PS_VERSION_')) {
    exit;
}

final class InvoicePdfTemplateBuilder implements TemplateBuilderInterface
{
    /**
     * @var Order
     */
    private $order;
    /** @var MolOrderPaymentFeeRepositoryInterface */
    private $molOrderPaymentFeeRepository;
    /** @var Locale */
    private $locale;
    /** @var CurrencyRepositoryInterface */
    private $currencyRepository;

    public function __construct(
        MolOrderPaymentFeeRepositoryInterface $molOrderPaymentFeeRepository,
        CurrencyRepositoryInterface $currencyRepository
    ) {
        $this->molOrderPaymentFeeRepository = $molOrderPaymentFeeRepository;
        $this->currencyRepository = $currencyRepository;
    }

    public function setOrder(Order $order): InvoicePdfTemplateBuilder
    {
        $this->order = $order;

        return $this;
    }

    public function setLocale(Locale $locale): InvoicePdfTemplateBuilder
    {
        $this->locale = $locale;

        return $this;
    }

    public function buildParams(): array
    {
        /** @var MolOrderPaymentFee|null $molOrderPaymentFee */
        $molOrderPaymentFee = $this->molOrderPaymentFeeRepository->findOneBy([
            'id_order' => (int) $this->order->id,
        ]);

        if (!$molOrderPaymentFee) {
            return [];
        }

        /** @var Currency|null $orderCurrency */
        $orderCurrency = $this->currencyRepository->findOneBy([
            'id_currency' => $this->order->id_currency,
            'deleted' => 0,
            'active' => 1,
        ]);

        if (!$orderCurrency) {
            return [];
        }

        return [
            'orderFeeAmountDisplay' => $this->locale->formatPrice(
                $molOrderPaymentFee->fee_tax_incl,
                $orderCurrency->iso_code
            ),
        ];
    }
}
