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

class CouldNotPresentOrderDetail extends MollieSubscriptionException
{
    public static function failedToFindOrder(): self
    {
        return new self(
            'Failed to find order',
            ExceptionCode::ORDER_FAILED_TO_FIND_ORDER
        );
    }

    public static function failedToFindOrderDetail(): self
    {
        return new self(
            'Failed to find order detail',
            ExceptionCode::ORDER_FAILED_TO_FIND_ORDER_DETAIL
        );
    }

    public static function failedToFindProduct(): self
    {
        return new self(
            'Failed to find product',
            ExceptionCode::ORDER_FAILED_TO_FIND_PRODUCT
        );
    }

    public static function failedToFindCurrency(): self
    {
        return new self(
            'Failed to find currency',
            ExceptionCode::ORDER_FAILED_TO_FIND_CURRENCY
        );
    }
}
