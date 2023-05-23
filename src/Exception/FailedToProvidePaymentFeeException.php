<?php

namespace Mollie\Exception;

class FailedToProvidePaymentFeeException extends \Exception
{
    public const FAILED_TO_FIND_TAX_RULES = 1;
    public const FAILED_TO_FIND_TAX = 2;
}
