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

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Exception\NotImplementedException;

class PrestaLogger implements PrestaLoggerInterface
{
    // TODO refactor whole logger logic and implement leftover methods

    /** @var ConfigurationAdapter */
    private $configuration;

    public function __construct(ConfigurationAdapter $configuration)
    {
        $this->configuration = $configuration;
    }

    public function emergency($message, array $context = [])
    {
        throw new NotImplementedException('not implemented method');
    }

    public function alert($message, array $context = [])
    {
        throw new NotImplementedException('not implemented method');
    }

    public function critical($message, array $context = [])
    {
        throw new NotImplementedException('not implemented method');
    }

    public function error($message, array $context = [])
    {
        if ((int) $this->configuration->get(Config::MOLLIE_DEBUG_LOG) === Config::DEBUG_LOG_NONE) {
            return;
        }

        $uniqueMessage = sprintf('Log ID (%s) | %s', uniqid('', true), $message);

        \PrestaShopLogger::addLog(
            $this->getMessageWithContext($uniqueMessage, $context),
            3
        );
    }

    public function warning($message, array $context = [])
    {
        throw new NotImplementedException('not implemented method');
    }

    public function notice($message, array $context = [])
    {
        throw new NotImplementedException('not implemented method');
    }

    public function info($message, array $context = [])
    {
        if ((int) $this->configuration->get(Config::MOLLIE_DEBUG_LOG) !== Config::DEBUG_LOG_ALL) {
            return;
        }

        $uniqueMessage = sprintf('Log ID (%s) | %s', uniqid('', true), $message);

        \PrestaShopLogger::addLog(
            $this->getMessageWithContext($uniqueMessage, $context)
        );
    }

    public function debug($message, array $context = [])
    {
        throw new NotImplementedException('not implemented method');
    }

    public function log($level, $message, array $context = [])
    {
        throw new NotImplementedException('not implemented method');
    }

    private function getMessageWithContext($message, array $context = [])
    {
        $content = json_encode($context);

        return "{$message} . context: {$content}";
    }
}
