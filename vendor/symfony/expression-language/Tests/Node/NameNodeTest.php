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

use MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode;
class NameNodeTest extends \MolliePrefix\Symfony\Component\ExpressionLanguage\Tests\Node\AbstractNodeTest
{
    public function getEvaluateData()
    {
        return [['bar', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), ['foo' => 'bar']]];
    }
    public function getCompileData()
    {
        return [['$foo', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo')]];
    }
    public function getDumpData()
    {
        return [['foo', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo')]];
    }
}
