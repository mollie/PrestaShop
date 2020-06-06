<?php

namespace _PhpScoper5ea00cc67502b\Psr\Log;

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
    public function setLogger(\_PhpScoper5ea00cc67502b\Psr\Log\LoggerInterface $logger);
}
