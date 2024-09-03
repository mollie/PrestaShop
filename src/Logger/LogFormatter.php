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

namespace Mollie\Logger;

if (!defined('_PS_VERSION_')) {
    exit;
}

class LogFormatter implements LogFormatterInterface
{
    const MOLLIE_LOG_PREFIX = 'MOLLIE_MODULE_LOG:';

    public function getMessage(string $message): string
    {
        return self::MOLLIE_LOG_PREFIX . ' ' . $message;
    }
}