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

class CouldNotUpdateOrderTotals extends MollieException
{
    public static function failedToUpdateOrderTotals(Exception $exception): self
    {
        return new self(
            'Failed to update order totals.',
            ExceptionCode::ORDER_FAILED_TO_UPDATE_ORDER_TOTALS,
            $exception
        );
    }
}
