<?php

namespace MolliePrefix\GuzzleHttp\Promise;

/**
 * Exception that is set as the reason for a promise that has been cancelled.
 */
class CancellationException extends \MolliePrefix\GuzzleHttp\Promise\RejectionException
{
}
