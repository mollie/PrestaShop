<?php

namespace _PhpScoper5eddef0da618a\Psr\Log;

/**
 * Basic Implementation of LoggerAwareInterface.
 */
trait LoggerAwareTrait
{
    /**
     * The logger instance.
     *
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * Sets a logger.
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(\_PhpScoper5eddef0da618a\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
