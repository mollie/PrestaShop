<?php

/*
 * This file is part of the Prophecy.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *     Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Prophecy\Promise;

use MolliePrefix\Prophecy\Exception\InvalidArgumentException;
use MolliePrefix\Prophecy\Prophecy\ObjectProphecy;
use MolliePrefix\Prophecy\Prophecy\MethodProphecy;
/**
 * Return argument promise.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ReturnArgumentPromise implements \MolliePrefix\Prophecy\Promise\PromiseInterface
{
    /**
     * @var int
     */
    private $index;
    /**
     * Initializes callback promise.
     *
     * @param int $index The zero-indexed number of the argument to return
     *
     * @throws \Prophecy\Exception\InvalidArgumentException
     */
    public function __construct($index = 0)
    {
        if (!\is_int($index) || $index < 0) {
            throw new \MolliePrefix\Prophecy\Exception\InvalidArgumentException(\sprintf('Zero-based index expected as argument to ReturnArgumentPromise, but got %s.', $index));
        }
        $this->index = $index;
    }
    /**
     * Returns nth argument if has one, null otherwise.
     *
     * @param array          $args
     * @param ObjectProphecy $object
     * @param MethodProphecy $method
     *
     * @return null|mixed
     */
    public function execute(array $args, \MolliePrefix\Prophecy\Prophecy\ObjectProphecy $object, \MolliePrefix\Prophecy\Prophecy\MethodProphecy $method)
    {
        return \count($args) > $this->index ? $args[$this->index] : null;
    }
}
