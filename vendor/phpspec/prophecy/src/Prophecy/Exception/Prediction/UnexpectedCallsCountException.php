<?php

/*
 * This file is part of the Prophecy.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *     Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Prophecy\Exception\Prediction;

use MolliePrefix\Prophecy\Prophecy\MethodProphecy;
class UnexpectedCallsCountException extends \MolliePrefix\Prophecy\Exception\Prediction\UnexpectedCallsException
{
    private $expectedCount;
    public function __construct($message, \MolliePrefix\Prophecy\Prophecy\MethodProphecy $methodProphecy, $count, array $calls)
    {
        parent::__construct($message, $methodProphecy, $calls);
        $this->expectedCount = \intval($count);
    }
    public function getExpectedCount()
    {
        return $this->expectedCount;
    }
}
