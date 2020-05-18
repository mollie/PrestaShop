<?php

namespace _PhpScoper5ea00cc67502b\GuzzleHttp\Promise;

use JsonSerializable;
use RuntimeException;
use function is_object;
use function is_string;
use function json_encode;
use function method_exists;
use const JSON_PRETTY_PRINT;

/**
 * A special exception that is thrown when waiting on a rejected promise.
 *
 * The reason value is available via the getReason() method.
 */
class RejectionException extends RuntimeException
{
    /** @var mixed Rejection reason. */
    private $reason;
    /**
     * @param mixed $reason       Rejection reason.
     * @param string $description Optional description
     */
    public function __construct($reason, $description = null)
    {
        $this->reason = $reason;
        $message = 'The promise was rejected';
        if ($description) {
            $message .= ' with reason: ' . $description;
        } elseif (is_string($reason) || is_object($reason) && method_exists($reason, '__toString')) {
            $message .= ' with reason: ' . $this->reason;
        } elseif ($reason instanceof JsonSerializable) {
            $message .= ' with reason: ' . json_encode($this->reason, JSON_PRETTY_PRINT);
        }
        parent::__construct($message);
    }
    /**
     * Returns the rejection reason.
     *
     * @return mixed
     */
    public function getReason()
    {
        return $this->reason;
    }
}
