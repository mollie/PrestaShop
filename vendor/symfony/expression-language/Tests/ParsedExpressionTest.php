<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\ExpressionLanguage\Tests;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode;
use MolliePrefix\Symfony\Component\ExpressionLanguage\ParsedExpression;
class ParsedExpressionTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testSerialization()
    {
        $expression = new \MolliePrefix\Symfony\Component\ExpressionLanguage\ParsedExpression('25', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode('25'));
        $serializedExpression = \serialize($expression);
        $unserializedExpression = \unserialize($serializedExpression);
        $this->assertEquals($expression, $unserializedExpression);
    }
}
