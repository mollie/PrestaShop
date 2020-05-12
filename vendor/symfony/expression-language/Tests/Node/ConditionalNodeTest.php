<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Tests\Node;

use _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConditionalNode;
use _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConstantNode;
class ConditionalNodeTest extends \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Tests\Node\AbstractNodeTest
{
    public function getEvaluateData()
    {
        return [[1, new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConditionalNode(new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true), new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConstantNode(1), new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConstantNode(2))], [2, new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConditionalNode(new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\false), new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConstantNode(1), new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConstantNode(2))]];
    }
    public function getCompileData()
    {
        return [['((true) ? (1) : (2))', new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConditionalNode(new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true), new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConstantNode(1), new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConstantNode(2))], ['((false) ? (1) : (2))', new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConditionalNode(new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\false), new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConstantNode(1), new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConstantNode(2))]];
    }
    public function getDumpData()
    {
        return [['(true ? 1 : 2)', new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConditionalNode(new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true), new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConstantNode(1), new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConstantNode(2))], ['(false ? 1 : 2)', new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConditionalNode(new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\false), new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConstantNode(1), new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConstantNode(2))]];
    }
}
