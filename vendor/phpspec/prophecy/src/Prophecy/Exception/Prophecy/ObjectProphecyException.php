<?php

/*
 * This file is part of the Prophecy.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *     Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Prophecy\Exception\Prophecy;

use MolliePrefix\Prophecy\Prophecy\ObjectProphecy;
class ObjectProphecyException extends \RuntimeException implements \MolliePrefix\Prophecy\Exception\Prophecy\ProphecyException
{
    private $objectProphecy;
    public function __construct($message, \MolliePrefix\Prophecy\Prophecy\ObjectProphecy $objectProphecy)
    {
        parent::__construct($message);
        $this->objectProphecy = $objectProphecy;
    }
    /**
     * @return ObjectProphecy
     */
    public function getObjectProphecy()
    {
        return $this->objectProphecy;
    }
}
