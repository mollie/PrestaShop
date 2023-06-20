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
use Mollie\Adapter\Context;
use Mollie\Repository\MolOrderPaymentFeeRepositoryInterface;
use MolOrderPaymentFee;
use Order;

final class InvoicePdfTemplateBuilder implements TemplateBuilderInterface
{
    /**
     * @var Order
     */
    private $order;
    /** @var MolOrderPaymentFeeRepositoryInterface */
    private $molOrderPaymentFeeRepository;
    /** @var Context */
    private $context;

    public function __construct(
        MolOrderPaymentFeeRepositoryInterface $molOrderPaymentFeeRepository,
        Context $context
    ) {
        $this->molOrderPaymentFeeRepository = $molOrderPaymentFeeRepository;
        $this->context = $context;
    }

    public function setOrder(Order $order): InvoicePdfTemplateBuilder
    {
        $this->order = $order;

        return $this;
    }

    public function buildParams(): array
    {
        /** @var MolOrderPaymentFee|null $molOrderPaymentFee */
        $molOrderPaymentFee = $this->molOrderPaymentFeeRepository->findOneBy([
            'id_order' => (int) $this->order->id,
        ]);

        if (!$molOrderPaymentFee || !$molOrderPaymentFee->id_order) {
            return [];
        }

        return [
            'orderFeeAmountDisplay' => $this->context->getCurrentLocale()->formatPrice(
                $molOrderPaymentFee->fee_tax_incl,
                (new Currency($this->order->id_currency))->iso_code
            ),
        ];
    }
}
