<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\Config\Definition\Builder;

use _PhpScoper5ea00cc67502b\Symfony\Component\Config\Definition\IntegerNode;
/**
 * This class provides a fluent interface for defining an integer node.
 *
 * @author Jeanmonod David <david.jeanmonod@gmail.com>
 */
class IntegerNodeDefinition extends \_PhpScoper5ea00cc67502b\Symfony\Component\Config\Definition\Builder\NumericNodeDefinition
{
    /**
     * Instantiates a Node.
     *
     * @return IntegerNode The node
     */
    protected function instantiateNode()
    {
        return new \_PhpScoper5ea00cc67502b\Symfony\Component\Config\Definition\IntegerNode($this->name, $this->parent, $this->min, $this->max);
    }
}
