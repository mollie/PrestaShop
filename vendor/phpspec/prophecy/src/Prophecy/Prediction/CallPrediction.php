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
use MolliePrefix\Prophecy\Exception\Prediction\NoCallsException;
/**
 * Call prediction.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class CallPrediction implements \MolliePrefix\Prophecy\Prediction\PredictionInterface
{
    private $util;
    /**
     * Initializes prediction.
     *
     * @param StringUtil $util
     */
    public function __construct(\MolliePrefix\Prophecy\Util\StringUtil $util = null)
    {
        $this->util = $util ?: new \MolliePrefix\Prophecy\Util\StringUtil();
    }
    /**
     * Tests that there was at least one call.
     *
     * @param Call[]         $calls
     * @param ObjectProphecy $object
     * @param MethodProphecy $method
     *
     * @throws \Prophecy\Exception\Prediction\NoCallsException
     */
    public function check(array $calls, \MolliePrefix\Prophecy\Prophecy\ObjectProphecy $object, \MolliePrefix\Prophecy\Prophecy\MethodProphecy $method)
    {
        if (\count($calls)) {
            return;
        }
        $methodCalls = $object->findProphecyMethodCalls($method->getMethodName(), new \MolliePrefix\Prophecy\Argument\ArgumentsWildcard(array(new \MolliePrefix\Prophecy\Argument\Token\AnyValuesToken())));
        if (\count($methodCalls)) {
            throw new \MolliePrefix\Prophecy\Exception\Prediction\NoCallsException(\sprintf("No calls have been made that match:\n" . "  %s->%s(%s)\n" . "but expected at least one.\n" . "Recorded `%s(...)` calls:\n%s", \get_class($object->reveal()), $method->getMethodName(), $method->getArgumentsWildcard(), $method->getMethodName(), $this->util->stringifyCalls($methodCalls)), $method);
        }
        throw new \MolliePrefix\Prophecy\Exception\Prediction\NoCallsException(\sprintf("No calls have been made that match:\n" . "  %s->%s(%s)\n" . "but expected at least one.", \get_class($object->reveal()), $method->getMethodName(), $method->getArgumentsWildcard()), $method);
    }
}
