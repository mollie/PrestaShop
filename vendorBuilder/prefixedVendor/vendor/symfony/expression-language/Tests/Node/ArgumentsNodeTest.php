<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\ExpressionLanguage\Tests\Node;

use MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ArgumentsNode;
class ArgumentsNodeTest extends \MolliePrefix\Symfony\Component\ExpressionLanguage\Tests\Node\ArrayNodeTest
{
    public function getCompileData()
    {
        return [['"a", "b"', $this->getArrayNode()]];
    }
    public function getDumpData()
    {
        return [['"a", "b"', $this->getArrayNode()]];
    }
    protected function createArrayNode()
    {
        return new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ArgumentsNode();
    }
}
