<?php

namespace MolliePrefix\Symfony\Component\Debug\Tests\Fixtures;

use MolliePrefix\Symfony\Component\Debug\BufferingLogger;
class LoggerThatSetAnErrorHandler extends \MolliePrefix\Symfony\Component\Debug\BufferingLogger
{
    public function log($level, $message, array $context = [])
    {
        \set_error_handler('is_string');
        parent::log($level, $message, $context);
        \restore_error_handler();
    }
}
