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

use MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConditionalNode;
use MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode;
class ConditionalNodeTest extends \MolliePrefix\Symfony\Component\ExpressionLanguage\Tests\Node\AbstractNodeTest
{
    public function getEvaluateData()
    {
        return [[1, new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConditionalNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(1), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(2))], [2, new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConditionalNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\false), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(1), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(2))]];
    }
    public function getCompileData()
    {
        return [['((true) ? (1) : (2))', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConditionalNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(1), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(2))], ['((false) ? (1) : (2))', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConditionalNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\false), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(1), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(2))]];
    }
    public function getDumpData()
    {
        return [['(true ? 1 : 2)', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConditionalNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(1), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(2))], ['(false ? 1 : 2)', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConditionalNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\false), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(1), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(2))]];
    }
}
