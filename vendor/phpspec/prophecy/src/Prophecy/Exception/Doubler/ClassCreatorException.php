<?php

/*
 * This file is part of the Prophecy.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *     Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Prophecy\Exception\Doubler;

use MolliePrefix\Prophecy\Doubler\Generator\Node\ClassNode;
class ClassCreatorException extends \RuntimeException implements \MolliePrefix\Prophecy\Exception\Doubler\DoublerException
{
    private $node;
    public function __construct($message, \MolliePrefix\Prophecy\Doubler\Generator\Node\ClassNode $node)
    {
        parent::__construct($message);
        $this->node = $node;
    }
    public function getClassNode()
    {
        return $this->node;
    }
}
