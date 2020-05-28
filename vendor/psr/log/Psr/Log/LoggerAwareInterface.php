<?php

namespace _PhpScoper5ece82d7231e4\Psr\Log;

/**
 * Describes a logger-aware instance.
 */
interface LoggerAwareInterface
{
    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(\_PhpScoper5ece82d7231e4\Psr\Log\LoggerInterface $logger);
}
