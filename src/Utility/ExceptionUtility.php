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

namespace Mollie\Utility;

use Configuration;
use Mollie\Config\Config;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ExceptionUtility
{
    public static function getExceptions(\Throwable $exception)
    {
        if (method_exists($exception, 'getExceptions')) {
            return $exception->getExceptions();
        }

        return [self::toArray($exception)];
    }

    public static function toArray(\Throwable $exception): array
    {
        if (method_exists($exception, 'getContext')) {
            $context = $exception->getContext();
        } else {
            $context = [];
        }

        return [
            'message' => (string) $exception->getMessage(),
            'code' => (int) $exception->getCode(),
            'file' => (string) $exception->getFile(),
            'line' => (int) $exception->getLine(),
            'context' => $context,
        ];
    }
}
