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
use MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode;
use MolliePrefix\Symfony\Component\ExpressionLanguage\Node\Node;
class NodeTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testToString()
    {
        $node = new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\Node([new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode('foo')]);
        $this->assertEquals(<<<'EOF'
Node(
    ConstantNode(value: 'foo')
)
EOF
, (string) $node);
    }
    public function testSerialization()
    {
        $node = new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\Node(['foo' => 'bar'], ['bar' => 'foo']);
        $serializedNode = \serialize($node);
        $unserializedNode = \unserialize($serializedNode);
        $this->assertEquals($node, $unserializedNode);
    }
}
