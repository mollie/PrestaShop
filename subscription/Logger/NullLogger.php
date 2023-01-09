<?php

declare(strict_types=1);

namespace Mollie\Subscription\Logger;

//NOTE only should be used for tests
final class NullLogger implements LoggerInterface
{
    public function emergency($message, array $context = [])
    {
        return null;
    }

    public function alert($message, array $context = [])
    {
        return null;
    }

    public function critical($message, array $context = [])
    {
        return null;
    }

    public function error($message, array $context = [])
    {
        return null;
    }

    public function warning($message, array $context = [])
    {
        return null;
    }

    public function notice($message, array $context = [])
    {
        return null;
    }

    public function info($message, array $context = [])
    {
        return null;
    }

    public function debug($message, array $context = [])
    {
        return null;
    }

    public function log($level, $message, array $context = [])
    {
        return null;
    }
}
