<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Tests\Node;

use _PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode;
use _PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\UnaryNode;
class UnaryNodeTest extends \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Tests\Node\AbstractNodeTest
{
    public function getEvaluateData()
    {
        return [[-1, new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\UnaryNode('-', new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode(1))], [3, new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\UnaryNode('+', new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode(3))], [\false, new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\UnaryNode('!', new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true))], [\false, new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\UnaryNode('not', new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true))]];
    }
    public function getCompileData()
    {
        return [['(-1)', new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\UnaryNode('-', new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode(1))], ['(+3)', new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\UnaryNode('+', new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode(3))], ['(!true)', new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\UnaryNode('!', new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true))], ['(!true)', new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\UnaryNode('not', new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true))]];
    }
    public function getDumpData()
    {
        return [['(- 1)', new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\UnaryNode('-', new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode(1))], ['(+ 3)', new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\UnaryNode('+', new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode(3))], ['(! true)', new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\UnaryNode('!', new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true))], ['(not true)', new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\UnaryNode('not', new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true))]];
    }
}
