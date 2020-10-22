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
use MolliePrefix\Prophecy\Exception\Prophecy\MethodProphecyException;
class UnexpectedCallsException extends \MolliePrefix\Prophecy\Exception\Prophecy\MethodProphecyException implements \MolliePrefix\Prophecy\Exception\Prediction\PredictionException
{
    private $calls = array();
    public function __construct($message, \MolliePrefix\Prophecy\Prophecy\MethodProphecy $methodProphecy, array $calls)
    {
        parent::__construct($message, $methodProphecy);
        $this->calls = $calls;
    }
    public function getCalls()
    {
        return $this->calls;
    }
}
