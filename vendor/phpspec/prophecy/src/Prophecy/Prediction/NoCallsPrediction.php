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
use MolliePrefix\Prophecy\Util\StringUtil;
use MolliePrefix\Prophecy\Exception\Prediction\UnexpectedCallsException;
/**
 * No calls prediction.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class NoCallsPrediction implements \MolliePrefix\Prophecy\Prediction\PredictionInterface
{
    private $util;
    /**
     * Initializes prediction.
     *
     * @param null|StringUtil $util
     */
    public function __construct(\MolliePrefix\Prophecy\Util\StringUtil $util = null)
    {
        $this->util = $util ?: new \MolliePrefix\Prophecy\Util\StringUtil();
    }
    /**
     * Tests that there were no calls made.
     *
     * @param Call[]         $calls
     * @param ObjectProphecy $object
     * @param MethodProphecy $method
     *
     * @throws \Prophecy\Exception\Prediction\UnexpectedCallsException
     */
    public function check(array $calls, \MolliePrefix\Prophecy\Prophecy\ObjectProphecy $object, \MolliePrefix\Prophecy\Prophecy\MethodProphecy $method)
    {
        if (!\count($calls)) {
            return;
        }
        $verb = \count($calls) === 1 ? 'was' : 'were';
        throw new \MolliePrefix\Prophecy\Exception\Prediction\UnexpectedCallsException(\sprintf("No calls expected that match:\n" . "  %s->%s(%s)\n" . "but %d %s made:\n%s", \get_class($object->reveal()), $method->getMethodName(), $method->getArgumentsWildcard(), \count($calls), $verb, $this->util->stringifyCalls($calls)), $method, $calls);
    }
}
