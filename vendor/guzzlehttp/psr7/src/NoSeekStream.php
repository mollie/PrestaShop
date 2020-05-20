<?php

namespace _PhpScoper5ea00cc67502b\GuzzleHttp\Psr7;

use _PhpScoper5ea00cc67502b\Psr\Http\Message\StreamInterface;
use RuntimeException;
use const SEEK_SET;

/**
 * Stream decorator that prevents a stream from being seeked
 */
class NoSeekStream implements StreamInterface
{
    use StreamDecoratorTrait;
    public function seek($offset, $whence = SEEK_SET)
    {
        throw new RuntimeException('Cannot seek a NoSeekStream');
    }
    public function isSeekable()
    {
        return false;
    }
}
