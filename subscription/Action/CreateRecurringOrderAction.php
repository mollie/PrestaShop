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

namespace Mollie\Subscription\Action;

use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Subscription\DTO\CreateRecurringOrderData;
use Mollie\Subscription\Exception\CouldNotCreateRecurringOrder;
use Mollie\Subscription\Exception\MollieSubscriptionException;
use Mollie\Subscription\Utility\ClockInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CreateRecurringOrderAction
{
    /** @var PrestaLoggerInterface */
    private $logger;
    /** @var ClockInterface */
    private $clock;

    public function __construct(
        PrestaLoggerInterface $logger,
        ClockInterface $clock
    ) {
        $this->logger = $logger;
        $this->clock = $clock;
    }

    /**
     * @throws MollieSubscriptionException
     */
    public function run(CreateRecurringOrderData $data): \MolRecurringOrder
    {
        $this->logger->debug(sprintf('%s - Function called', __METHOD__));

        try {
            $recurringOrder = new \MolRecurringOrder();

            $recurringOrder->id_mol_recurring_orders_product = $data->getRecurringOrdersProductId();
            $recurringOrder->id_order = $data->getOrderId();
            $recurringOrder->id_cart = $data->getCartId();
            $recurringOrder->id_currency = $data->getCurrencyId();
            $recurringOrder->id_customer = $data->getCustomerId();
            $recurringOrder->id_address_delivery = $data->getDeliveryAddressId();
            $recurringOrder->id_address_invoice = $data->getInvoiceAddressId();
            $recurringOrder->description = $data->getDescription();
            $recurringOrder->status = $data->getStatus();
            $recurringOrder->total_tax_incl = $data->getSubscriptionTotalAmount();
            $recurringOrder->payment_method = $data->getMethod();
            $recurringOrder->next_payment = $data->getNextPayment();
            $recurringOrder->reminder_at = $data->getReminderAt();
            $recurringOrder->cancelled_at = $data->getCancelledAt();
            $recurringOrder->mollie_subscription_id = $data->getMollieSubscriptionId();
            $recurringOrder->mollie_customer_id = $data->getMollieCustomerId();
            $recurringOrder->date_add = $this->clock->getCurrentDate();
            $recurringOrder->date_update = $this->clock->getCurrentDate();

            $recurringOrder->add();
        } catch (\Throwable $exception) {
            throw CouldNotCreateRecurringOrder::unknownError($exception);
        }

        $this->logger->debug(sprintf('%s - Function ended', __METHOD__));

        return $recurringOrder;
    }
}
