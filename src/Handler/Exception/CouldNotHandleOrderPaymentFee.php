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

namespace Mollie\Handler\Exception;

use Mollie\Exception\Code\ExceptionCode;
use Mollie\Exception\MollieException;
use Throwable;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CouldNotHandleOrderPaymentFee extends MollieException
{
    public static function failedToRetrievePaymentMethod(Throwable $exception): self
    {
        return new self(
            'Failed to retrieve payment method',
            ExceptionCode::ORDER_FAILED_TO_RETRIEVE_PAYMENT_METHOD,
            $exception
        );
    }

    public static function failedToRetrievePaymentFee(Throwable $exception): self
    {
        return new self(
            'Failed to retrieve payment fee',
            ExceptionCode::ORDER_FAILED_TO_RETRIEVE_PAYMENT_FEE,
            $exception
        );
    }

    public static function failedToCreateOrderPaymentFee(Throwable $exception): self
    {
        return new self(
            'Failed to create order payment fee',
            ExceptionCode::ORDER_FAILED_TO_CREATE_ORDER_PAYMENT_FEE,
            $exception
        );
    }

    public static function failedToUpdateOrderTotalWithPaymentFee(Throwable $exception): self
    {
        return new self(
            'Failed to update order total with payment fee.',
            ExceptionCode::ORDER_FAILED_TO_UPDATE_ORDER_TOTAL_WITH_PAYMENT_FEE,
            $exception
        );
    }
}
