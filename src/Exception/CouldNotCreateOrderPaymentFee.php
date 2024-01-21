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

namespace Mollie\Exception;

use Exception;
use Mollie\Exception\Code\ExceptionCode;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CouldNotCreateOrderPaymentFee extends MollieException
{
    public static function failedToInsertOrderPaymentFee(Exception $exception): self
    {
        return new self(
            'Failed to insert order payment fee.',
            ExceptionCode::ORDER_FAILED_TO_INSERT_ORDER_PAYMENT_FEE,
            $exception
        );
    }
}
