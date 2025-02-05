<?php

declare(strict_types=1);

namespace Mollie\Logger;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Exception\NotImplementedException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PrestaLogger implements PrestaLoggerInterface
{
    /** @var ConfigurationAdapter */
    private $configuration;

    public function __construct(ConfigurationAdapter $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param string|\Stringable $message
     */
    public function emergency($message, array $context = []): void
    {
        $this->validateMessage($message);
        $this->logWithPrestaShop('emergency', $message, $context);
    }

    /**
     * @param string|\Stringable $message
     */
    public function alert($message, array $context = []): void
    {
        $this->validateMessage($message);
        $this->logWithPrestaShop('alert', $message, $context);
    }

    /**
     * @param string|\Stringable $message
     */
    public function critical($message, array $context = []): void
    {
        $this->validateMessage($message);
        $this->logWithPrestaShop('critical', $message, $context);
    }

    /**
     * @param string|\Stringable $message
     */
    public function error($message, array $context = []): void
    {
        $this->validateMessage($message);

        if ((int) $this->configuration->get(Config::MOLLIE_DEBUG_LOG) === Config::DEBUG_LOG_NONE) {
            return;
        }

        $uniqueMessage = sprintf('Log ID (%s) | %s', uniqid('', true), $message);

        \PrestaShopLogger::addLog(
            $this->getMessageWithContext($uniqueMessage, $context),
            3
        );
    }

    /**
     * @param string|\Stringable $message
     */
    public function warning($message, array $context = []): void
    {
        $this->validateMessage($message);
        $this->logWithPrestaShop('warning', $message, $context);
    }

    /**
     * @param string|\Stringable $message
     */
    public function notice($message, array $context = []): void
    {
        $this->validateMessage($message);
        $this->logWithPrestaShop('notice', $message, $context);
    }

    /**
     * @param string|\Stringable $message
     */
    public function info($message, array $context = []): void
    {
        $this->validateMessage($message);

        if ((int) $this->configuration->get(Config::MOLLIE_DEBUG_LOG) !== Config::DEBUG_LOG_ALL) {
            return;
        }

        $uniqueMessage = sprintf('Log ID (%s) | %s', uniqid('', true), $message);

        \PrestaShopLogger::addLog(
            $this->getMessageWithContext($uniqueMessage, $context)
        );
    }

    /**
     * @param string|\Stringable $message
     */
    public function debug($message, array $context = []): void
    {
        $this->validateMessage($message);
        $this->info($message, $context);
    }

    /**
     * @param string|\Stringable $message
     */
    public function log($level, $message, array $context = []): void
    {
        $this->validateMessage($message);
        throw new NotImplementedException('not implemented method');
    }

    private function getMessageWithContext(string $message, array $context = []): string
    {
        $content = json_encode($context);

        return "{$message} . context: {$content}";
    }

    /**
     * Handle logging logic with PrestaShopLogger.
     *
     * @param string|\Stringable $message
     */
    private function logWithPrestaShop(string $level, $message, array $context): void
    {
        throw new NotImplementedException("Method {$level} not implemented.");
    }

    /**
     * Validate that the message is a string or Stringable.
     *
     * @param mixed $message
     *
     * @throws \InvalidArgumentException
     */
    private function validateMessage($message): void
    {
        if (!is_string($message) && !$message instanceof \Stringable) {
            throw new \InvalidArgumentException('Message must be a string or Stringable.');
        }
    }
}
