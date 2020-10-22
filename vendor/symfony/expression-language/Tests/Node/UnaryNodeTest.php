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

use MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode;
use MolliePrefix\Symfony\Component\ExpressionLanguage\Node\UnaryNode;
class UnaryNodeTest extends \MolliePrefix\Symfony\Component\ExpressionLanguage\Tests\Node\AbstractNodeTest
{
    public function getEvaluateData()
    {
        return [[-1, new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\UnaryNode('-', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(1))], [3, new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\UnaryNode('+', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(3))], [\false, new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\UnaryNode('!', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true))], [\false, new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\UnaryNode('not', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true))]];
    }
    public function getCompileData()
    {
        return [['(-1)', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\UnaryNode('-', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(1))], ['(+3)', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\UnaryNode('+', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(3))], ['(!true)', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\UnaryNode('!', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true))], ['(!true)', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\UnaryNode('not', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true))]];
    }
    public function getDumpData()
    {
        return [['(- 1)', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\UnaryNode('-', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(1))], ['(+ 3)', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\UnaryNode('+', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(3))], ['(! true)', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\UnaryNode('!', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true))], ['(not true)', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\UnaryNode('not', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true))]];
    }
}
