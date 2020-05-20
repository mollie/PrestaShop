<?php

namespace _PhpScoper5ea00cc67502b\Psr\Log;

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
    public function setLogger(\_PhpScoper5ea00cc67502b\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
