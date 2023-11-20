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

namespace Mollie\Subscription\Logger;

if (!defined('_PS_VERSION_')) {
    exit;
}

//NOTE only should be used for tests
final class NullLogger implements LoggerInterface
{
    /**
     * @return null
     */
    public function emergency($message, array $context = [])
    {
        return null;
    }

    /**
     * @return null
     */
    public function alert($message, array $context = [])
    {
        return null;
    }

    /**
     * @return null
     */
    public function critical($message, array $context = [])
    {
        return null;
    }

    /**
     * @return null
     */
    public function error($message, array $context = [])
    {
        return null;
    }

    /**
     * @return null
     */
    public function warning($message, array $context = [])
    {
        return null;
    }

    /**
     * @return null
     */
    public function notice($message, array $context = [])
    {
        return null;
    }

    /**
     * @return null
     */
    public function info($message, array $context = [])
    {
        return null;
    }

    /**
     * @return null
     */
    public function debug($message, array $context = [])
    {
        return null;
    }

    /**
     * @return null
     */
    public function log($level, $message, array $context = [])
    {
        return null;
    }
}
