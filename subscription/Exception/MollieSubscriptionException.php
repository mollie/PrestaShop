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

declare(strict_types=1);

namespace Mollie\Subscription\Exception;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MollieSubscriptionException extends \Exception
{
    public static function unknownError(\Throwable $exception): self
    {
        return new self(
            'An unknown error error occurred. Please check system logs or contact Mollie support.',
            ExceptionCode::UNKNOWN_ERROR,
            $exception
        );
    }
}
