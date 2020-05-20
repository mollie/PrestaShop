<?php

namespace _PhpScoper5ea00cc67502b\GuzzleHttp\Exception;

use _PhpScoper5ea00cc67502b\Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Exception thrown when a seek fails on a stream.
 */
class SeekException extends RuntimeException implements GuzzleException
{
    private $stream;
    public function __construct(StreamInterface $stream, $pos = 0, $msg = '')
    {
        $this->stream = $stream;
        $msg = $msg ?: 'Could not seek the stream to position ' . $pos;
        parent::__construct($msg);
    }
    /**
     * @return StreamInterface
     */
    public function getStream()
    {
        return $this->stream;
    }
}
