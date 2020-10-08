<?php

namespace MolliePrefix\GuzzleHttp\Promise;

final class Is
{
    /**
     * Returns true if a promise is pending.
     *
     * @return bool
     */
    public static function pending(\MolliePrefix\GuzzleHttp\Promise\PromiseInterface $promise)
    {
        return $promise->getState() === \MolliePrefix\GuzzleHttp\Promise\PromiseInterface::PENDING;
    }
    /**
     * Returns true if a promise is fulfilled or rejected.
     *
     * @return bool
     */
    public static function settled(\MolliePrefix\GuzzleHttp\Promise\PromiseInterface $promise)
    {
        return $promise->getState() !== \MolliePrefix\GuzzleHttp\Promise\PromiseInterface::PENDING;
    }
    /**
     * Returns true if a promise is fulfilled.
     *
     * @return bool
     */
    public static function fulfilled(\MolliePrefix\GuzzleHttp\Promise\PromiseInterface $promise)
    {
        return $promise->getState() === \MolliePrefix\GuzzleHttp\Promise\PromiseInterface::FULFILLED;
    }
    /**
     * Returns true if a promise is rejected.
     *
     * @return bool
     */
    public static function rejected(\MolliePrefix\GuzzleHttp\Promise\PromiseInterface $promise)
    {
        return $promise->getState() === \MolliePrefix\GuzzleHttp\Promise\PromiseInterface::REJECTED;
    }
}
