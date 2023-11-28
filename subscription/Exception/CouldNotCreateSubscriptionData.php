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

class CouldNotCreateSubscriptionData extends MollieSubscriptionException
{
    public static function failedToFindMollieCustomer(string $email): self
    {
        return new self(
            sprintf(
                'Failed to find Mollie customer. Email: (%s)',
                $email
            ),
            ExceptionCode::ORDER_FAILED_TO_FIND_MOLLIE_CUSTOMER
        );
    }

    public static function failedToRetrieveSubscriptionInterval(\Throwable $exception, int $productAttributeId): self
    {
        return new self(
            sprintf(
                'Failed to retrieve subscription interval. Product attribute ID: (%s)',
                $productAttributeId
            ),
            ExceptionCode::ORDER_FAILED_TO_RETRIEVE_SUBSCRIPTION_INTERVAL,
            $exception
        );
    }

    public static function failedToProvideCarrierDeliveryPrice(\Throwable $exception): self
    {
        return new self(
            'Failed to provide carrier delivery price.',
            ExceptionCode::ORDER_FAILED_TO_PROVIDE_CARRIER_DELIVERY_PRICE,
            $exception
        );
    }

    public static function failedToFindCurrency(int $currencyId): self
    {
        return new self(
            sprintf(
                'Failed to find currency. Currency ID: (%s)',
                $currencyId
            ),
            ExceptionCode::ORDER_FAILED_TO_FIND_CURRENCY
        );
    }
}
