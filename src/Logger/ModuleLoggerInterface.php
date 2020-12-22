<?php

namespace Mollie\Logger;

use Exception;

interface ModuleLoggerInterface
{
    /**
     * @param Exception $exception
     * @param string $message
     * @param int $severity
     */
    public function logException(Exception $exception, $message, $severity);
}