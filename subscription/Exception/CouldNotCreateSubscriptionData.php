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

    public static function failedToProvideSubscriptionOrderAmount(): self
    {
        return new self(
            'Failed to provide subscription order amount.',
            ExceptionCode::ORDER_FAILED_TO_PROVIDE_SUBSCRIPTION_ORDER_AMOUNT
        );
    }
}
