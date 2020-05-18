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

use _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConstantNode;
use _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\FunctionNode;
use _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\Node;
class FunctionNodeTest extends \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Tests\Node\AbstractNodeTest
{
    public function getEvaluateData()
    {
        return [['bar', new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\FunctionNode('foo', new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\Node([new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConstantNode('bar')])), [], ['foo' => $this->getCallables()]]];
    }
    public function getCompileData()
    {
        return [['foo("bar")', new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\FunctionNode('foo', new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\Node([new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConstantNode('bar')])), ['foo' => $this->getCallables()]]];
    }
    public function getDumpData()
    {
        return [['foo("bar")', new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\FunctionNode('foo', new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\Node([new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConstantNode('bar')])), ['foo' => $this->getCallables()]]];
    }
    protected function getCallables()
    {
        return ['compiler' => function ($arg) {
            return \sprintf('foo(%s)', $arg);
        }, 'evaluator' => function ($variables, $arg) {
            return $arg;
        }];
    }
}
