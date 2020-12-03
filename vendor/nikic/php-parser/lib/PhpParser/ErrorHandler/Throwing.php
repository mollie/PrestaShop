<?php

namespace MolliePrefix\PhpParser\ErrorHandler;

use MolliePrefix\PhpParser\Error;
use MolliePrefix\PhpParser\ErrorHandler;
/**
 * Error handler that handles all errors by throwing them.
 *
 * This is the default strategy used by all components.
 */
class Throwing implements \MolliePrefix\PhpParser\ErrorHandler
{
    public function handleError(\MolliePrefix\PhpParser\Error $error)
    {
        throw $error;
    }
}
