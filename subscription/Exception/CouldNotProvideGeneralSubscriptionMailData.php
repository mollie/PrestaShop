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

namespace Mollie\Subscription\Exception;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CouldNotProvideGeneralSubscriptionMailData extends MollieSubscriptionException
{
    public static function failedToFindRecurringOrder(int $recurringOrderId): self
    {
        return new self(
           sprintf(
               'Failed to find recurring order. Recurring order ID: (%s)',
               $recurringOrderId
           ),
           ExceptionCode::RECURRING_ORDER_FAILED_TO_FIND_RECURRING_ORDER
        );
    }

    public static function failedToFindRecurringOrderProduct(int $recurringOrderId, int $recurringOrderProductId): self
    {
        return new self(
            sprintf(
                'Failed to find recurring order product. Recurring order ID: (%s). Recurring product ID: (%s)',
                $recurringOrderId,
                $recurringOrderProductId
            ),
            ExceptionCode::RECURRING_ORDER_FAILED_TO_FIND_RECURRING_ORDER_PRODUCT
        );
    }

    public static function failedToFindCustomer(int $recurringOrderId, int $customerId): self
    {
        return new self(
            sprintf(
                'Failed to find customer. Recurring order ID: (%s). Customer ID: (%s)',
                $recurringOrderId,
                $customerId
            ),
            ExceptionCode::RECURRING_ORDER_FAILED_TO_FIND_CUSTOMER
        );
    }

    public static function failedToFindProduct(int $recurringOrderProductId, int $productId): self
    {
        return new self(
            sprintf(
                'Failed to find product. Recurring order product ID: (%s). Product ID: (%s)',
                $recurringOrderProductId,
                $productId
            ),
            ExceptionCode::RECURRING_ORDER_FAILED_TO_FIND_PRODUCT
        );
    }

    public static function failedToFindCurrency(int $recurringOrderId, int $currencyId): self
    {
        return new self(
            sprintf(
                'Failed to find currency. Recurring order ID: (%s). Currency ID: (%s)',
                $recurringOrderId,
                $currencyId
            ),
            ExceptionCode::RECURRING_ORDER_FAILED_TO_FIND_CURRENCY
        );
    }
}
