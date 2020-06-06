<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Tests;

use _PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase;
use _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConstantNode;
use _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\ParsedExpression;
class ParsedExpressionTest extends \_PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase
{
    public function testSerialization()
    {
        $expression = new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\ParsedExpression('25', new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConstantNode('25'));
        $serializedExpression = \serialize($expression);
        $unserializedExpression = \unserialize($serializedExpression);
        $this->assertEquals($expression, $unserializedExpression);
    }
}
