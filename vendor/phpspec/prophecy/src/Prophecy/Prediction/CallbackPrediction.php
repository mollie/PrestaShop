<?php

/*
 * This file is part of the Prophecy.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *     Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Prophecy\Prediction;

use MolliePrefix\Prophecy\Call\Call;
use MolliePrefix\Prophecy\Prophecy\ObjectProphecy;
use MolliePrefix\Prophecy\Prophecy\MethodProphecy;
use MolliePrefix\Prophecy\Exception\InvalidArgumentException;
use Closure;
/**
 * Callback prediction.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class CallbackPrediction implements \MolliePrefix\Prophecy\Prediction\PredictionInterface
{
    private $callback;
    /**
     * Initializes callback prediction.
     *
     * @param callable $callback Custom callback
     *
     * @throws \Prophecy\Exception\InvalidArgumentException
     */
    public function __construct($callback)
    {
        if (!\is_callable($callback)) {
            throw new \MolliePrefix\Prophecy\Exception\InvalidArgumentException(\sprintf('Callable expected as an argument to CallbackPrediction, but got %s.', \gettype($callback)));
        }
        $this->callback = $callback;
    }
    /**
     * Executes preset callback.
     *
     * @param Call[]         $calls
     * @param ObjectProphecy $object
     * @param MethodProphecy $method
     */
    public function check(array $calls, \MolliePrefix\Prophecy\Prophecy\ObjectProphecy $object, \MolliePrefix\Prophecy\Prophecy\MethodProphecy $method)
    {
        $callback = $this->callback;
        if ($callback instanceof \Closure && \method_exists('Closure', 'bind')) {
            $callback = \Closure::bind($callback, $object);
        }
        \call_user_func($callback, $calls, $object, $method);
    }
}
