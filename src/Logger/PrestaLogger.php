<?php

namespace Mollie\Logger;

use Mollie\Exception\NotImplementedException;
use Psr\Log\LoggerInterface;

class PrestaLogger implements LoggerInterface
{
    public function emergency($message, array $context = array())
    {
        throw new NotImplementedException('not implemented method');
    }

    public function alert($message, array $context = array())
    {
        throw new NotImplementedException('not implemented method');
    }

    public function critical($message, array $context = array())
    {
        throw new NotImplementedException('not implemented method');
    }

    public function error($message, array $context = array())
    {
        \PrestaShopLogger::addLog(
            $this->getMessageWithContext($message, $context),
            2
        );
    }

    public function warning($message, array $context = array())
    {
        throw new NotImplementedException('not implemented method');
    }

    public function notice($message, array $context = array())
    {
        throw new NotImplementedException('not implemented method');
    }

    public function info($message, array $context = array())
    {
        \PrestaShopLogger::addLog(
            $this->getMessageWithContext($message, $context)
        );
    }

    public function debug($message, array $context = array())
    {
        throw new NotImplementedException('not implemented method');
    }

    public function log($level, $message, array $context = array())
    {
        throw new NotImplementedException('not implemented method');
    }

    private function getMessageWithContext($message, array $context = array())
    {
        $content = json_encode($context);

        return  "{$message} . context: {$content}";
    }
}