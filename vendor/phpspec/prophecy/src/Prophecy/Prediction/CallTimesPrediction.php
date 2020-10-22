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
use MolliePrefix\Prophecy\Argument\ArgumentsWildcard;
use MolliePrefix\Prophecy\Argument\Token\AnyValuesToken;
use MolliePrefix\Prophecy\Util\StringUtil;
use MolliePrefix\Prophecy\Exception\Prediction\UnexpectedCallsCountException;
/**
 * Prediction interface.
 * Predictions are logical test blocks, tied to `should...` keyword.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class CallTimesPrediction implements \MolliePrefix\Prophecy\Prediction\PredictionInterface
{
    private $times;
    private $util;
    /**
     * Initializes prediction.
     *
     * @param int        $times
     * @param StringUtil $util
     */
    public function __construct($times, \MolliePrefix\Prophecy\Util\StringUtil $util = null)
    {
        $this->times = \intval($times);
        $this->util = $util ?: new \MolliePrefix\Prophecy\Util\StringUtil();
    }
    /**
     * Tests that there was exact amount of calls made.
     *
     * @param Call[]         $calls
     * @param ObjectProphecy $object
     * @param MethodProphecy $method
     *
     * @throws \Prophecy\Exception\Prediction\UnexpectedCallsCountException
     */
    public function check(array $calls, \MolliePrefix\Prophecy\Prophecy\ObjectProphecy $object, \MolliePrefix\Prophecy\Prophecy\MethodProphecy $method)
    {
        if ($this->times == \count($calls)) {
            return;
        }
        $methodCalls = $object->findProphecyMethodCalls($method->getMethodName(), new \MolliePrefix\Prophecy\Argument\ArgumentsWildcard(array(new \MolliePrefix\Prophecy\Argument\Token\AnyValuesToken())));
        if (\count($calls)) {
            $message = \sprintf("Expected exactly %d calls that match:\n" . "  %s->%s(%s)\n" . "but %d were made:\n%s", $this->times, \get_class($object->reveal()), $method->getMethodName(), $method->getArgumentsWildcard(), \count($calls), $this->util->stringifyCalls($calls));
        } elseif (\count($methodCalls)) {
            $message = \sprintf("Expected exactly %d calls that match:\n" . "  %s->%s(%s)\n" . "but none were made.\n" . "Recorded `%s(...)` calls:\n%s", $this->times, \get_class($object->reveal()), $method->getMethodName(), $method->getArgumentsWildcard(), $method->getMethodName(), $this->util->stringifyCalls($methodCalls));
        } else {
            $message = \sprintf("Expected exactly %d calls that match:\n" . "  %s->%s(%s)\n" . "but none were made.", $this->times, \get_class($object->reveal()), $method->getMethodName(), $method->getArgumentsWildcard());
        }
        throw new \MolliePrefix\Prophecy\Exception\Prediction\UnexpectedCallsCountException($message, $method, $this->times, $calls);
    }
}
