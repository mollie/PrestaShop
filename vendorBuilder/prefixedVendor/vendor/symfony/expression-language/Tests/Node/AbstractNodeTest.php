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

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\ExpressionLanguage\Compiler;
abstract class AbstractNodeTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getEvaluateData
     */
    public function testEvaluate($expected, $node, $variables = [], $functions = [])
    {
        $this->assertSame($expected, $node->evaluate($functions, $variables));
    }
    public abstract function getEvaluateData();
    /**
     * @dataProvider getCompileData
     */
    public function testCompile($expected, $node, $functions = [])
    {
        $compiler = new \MolliePrefix\Symfony\Component\ExpressionLanguage\Compiler($functions);
        $node->compile($compiler);
        $this->assertSame($expected, $compiler->getSource());
    }
    public abstract function getCompileData();
    /**
     * @dataProvider getDumpData
     */
    public function testDump($expected, $node)
    {
        $this->assertSame($expected, $node->dump());
    }
    public abstract function getDumpData();
}
