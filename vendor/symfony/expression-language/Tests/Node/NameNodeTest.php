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

use _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\NameNode;
class NameNodeTest extends \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Tests\Node\AbstractNodeTest
{
    public function getEvaluateData()
    {
        return [['bar', new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), ['foo' => 'bar']]];
    }
    public function getCompileData()
    {
        return [['$foo', new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\NameNode('foo')]];
    }
    public function getDumpData()
    {
        return [['foo', new \_PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\NameNode('foo')]];
    }
}
