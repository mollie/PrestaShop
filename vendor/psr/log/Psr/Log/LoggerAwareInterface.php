<?php

namespace _PhpScoper5eddef0da618a\Psr\Log;

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
    public function setLogger(\_PhpScoper5eddef0da618a\Psr\Log\LoggerInterface $logger);
}
