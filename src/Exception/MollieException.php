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

use Mollie\Exception\Code\ExceptionCode;
use Throwable;

class MollieException extends \Exception
{
    const CUSTOMER_EXCEPTION = 1;

    const API_CONNECTION_EXCEPTION = 2;

    final public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function unknownError(Throwable $exception): self
    {
        return new static(
            'An unknown error error occurred. Please check system logs or contact Klarna payment support.',
            ExceptionCode::INFRASTRUCTURE_UNKNOWN_ERROR,
            $exception
        );
    }
}
