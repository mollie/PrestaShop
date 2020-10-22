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
class ConstantNodeTest extends \MolliePrefix\Symfony\Component\ExpressionLanguage\Tests\Node\AbstractNodeTest
{
    public function getEvaluateData()
    {
        return [[\false, new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\false)], [\true, new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true)], [null, new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(null)], [3, new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(3)], [3.3, new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(3.3)], ['foo', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode('foo')], [[1, 'b' => 'a'], new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode([1, 'b' => 'a'])]];
    }
    public function getCompileData()
    {
        return [['false', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\false)], ['true', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true)], ['null', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(null)], ['3', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(3)], ['3.3', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(3.3)], ['"foo"', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode('foo')], ['[0 => 1, "b" => "a"]', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode([1, 'b' => 'a'])]];
    }
    public function getDumpData()
    {
        return [['false', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\false)], ['true', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true)], ['null', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(null)], ['3', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(3)], ['3.3', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(3.3)], ['"foo"', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode('foo')], ['foo', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode('foo', \true)], ['{0: 1, "b": "a", 1: true}', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode([1, 'b' => 'a', \true])], ['{"a\\"b": "c", "a\\\\b": "d"}', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(['a"b' => 'c', 'MolliePrefix\\a\\b' => 'd'])], ['["c", "d"]', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(['c', 'd'])], ['{"a": ["b"]}', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(['a' => ['b']])]];
    }
}
