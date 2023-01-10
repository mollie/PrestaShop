<?php

declare(strict_types=1);

namespace Mollie\Subscription\Logger;

class Logger implements LoggerInterface
{
    const FILE_NAME = 'Logger';

    const LOG_OBJECT_TYPE = 'mollie_sub_log';

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

    public function log($level, $message, array $context = []): void
    {
        \PrestaShopLogger::addLog(
            $message,
            $level,
            null,
            self::LOG_OBJECT_TYPE
        );
    }
}
