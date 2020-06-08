<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Tests;

use _PhpScoper5eddef0da618a\PHPUnit\Framework\TestCase;
use _PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Lexer;
use _PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node;
use _PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Parser;
class ParserTest extends \_PhpScoper5eddef0da618a\PHPUnit\Framework\TestCase
{
    public function testParseWithInvalidName()
    {
        $this->expectException('_PhpScoper5eddef0da618a\\Symfony\\Component\\ExpressionLanguage\\SyntaxError');
        $this->expectExceptionMessage('Variable "foo" is not valid around position 1 for expression `foo`.');
        $lexer = new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Lexer();
        $parser = new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Parser([]);
        $parser->parse($lexer->tokenize('foo'));
    }
    public function testParseWithZeroInNames()
    {
        $this->expectException('_PhpScoper5eddef0da618a\\Symfony\\Component\\ExpressionLanguage\\SyntaxError');
        $this->expectExceptionMessage('Variable "foo" is not valid around position 1 for expression `foo`.');
        $lexer = new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Lexer();
        $parser = new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Parser([]);
        $parser->parse($lexer->tokenize('foo'), [0]);
    }
    /**
     * @dataProvider getParseData
     */
    public function testParse($node, $expression, $names = [])
    {
        $lexer = new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Lexer();
        $parser = new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Parser([]);
        $this->assertEquals($node, $parser->parse($lexer->tokenize($expression), $names));
    }
    public function getParseData()
    {
        $arguments = new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ArgumentsNode();
        $arguments->addElement(new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode('arg1'));
        $arguments->addElement(new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode(2));
        $arguments->addElement(new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true));
        $arrayNode = new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ArrayNode();
        $arrayNode->addElement(new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\NameNode('bar'));
        return [
            [new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\NameNode('a'), 'a', ['a']],
            [new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode('a'), '"a"'],
            [new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode(3), '3'],
            [new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\false), 'false'],
            [new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true), 'true'],
            [new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode(null), 'null'],
            [new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\UnaryNode('-', new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode(3)), '-3'],
            [new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\BinaryNode('-', new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode(3), new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode(3)), '3 - 3'],
            [new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\BinaryNode('*', new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\BinaryNode('-', new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode(3), new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode(3)), new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode(2)), '(3 - 3) * 2'],
            [new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode('bar', \true), new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ArgumentsNode(), \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::PROPERTY_CALL), 'foo.bar', ['foo']],
            [new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode('bar', \true), new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ArgumentsNode(), \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::METHOD_CALL), 'foo.bar()', ['foo']],
            [new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode('not', \true), new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ArgumentsNode(), \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::METHOD_CALL), 'foo.not()', ['foo']],
            [new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode('bar', \true), $arguments, \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::METHOD_CALL), 'foo.bar("arg1", 2, true)', ['foo']],
            [new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode(3), new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ArgumentsNode(), \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::ARRAY_CALL), 'foo[3]', ['foo']],
            [new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConditionalNode(new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true), new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true), new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\false)), 'true ? true : false'],
            [new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\BinaryNode('matches', new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode('foo'), new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode('/foo/')), '"foo" matches "/foo/"'],
            // chained calls
            [$this->createGetAttrNode($this->createGetAttrNode($this->createGetAttrNode($this->createGetAttrNode(new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), 'bar', \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::METHOD_CALL), 'foo', \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::METHOD_CALL), 'baz', \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::PROPERTY_CALL), '3', \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::ARRAY_CALL), 'foo.bar().foo().baz[3]', ['foo']],
            [new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), 'bar', ['foo' => 'bar']],
            // Operators collisions
            [new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\BinaryNode('in', new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode('not', \true), new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ArgumentsNode(), \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::PROPERTY_CALL), $arrayNode), 'foo.not in [bar]', ['foo', 'bar']],
            [new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\BinaryNode('or', new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\UnaryNode('not', new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\NameNode('foo')), new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode('not', \true), new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ArgumentsNode(), \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::PROPERTY_CALL)), 'not foo or foo.not', ['foo']],
        ];
    }
    private function createGetAttrNode($node, $item, $type)
    {
        return new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\GetAttrNode($node, new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode($item, \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::ARRAY_CALL !== $type), new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ArgumentsNode(), $type);
    }
    /**
     * @dataProvider getInvalidPostfixData
     */
    public function testParseWithInvalidPostfixData($expr, $names = [])
    {
        $this->expectException('_PhpScoper5eddef0da618a\\Symfony\\Component\\ExpressionLanguage\\SyntaxError');
        $lexer = new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Lexer();
        $parser = new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Parser([]);
        $parser->parse($lexer->tokenize($expr), $names);
    }
    public function getInvalidPostfixData()
    {
        return [['foo."#"', ['foo']], ['foo."bar"', ['foo']], ['foo.**', ['foo']], ['foo.123', ['foo']]];
    }
    public function testNameProposal()
    {
        $this->expectException('_PhpScoper5eddef0da618a\\Symfony\\Component\\ExpressionLanguage\\SyntaxError');
        $this->expectExceptionMessage('Did you mean "baz"?');
        $lexer = new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Lexer();
        $parser = new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Parser([]);
        $parser->parse($lexer->tokenize('foo > bar'), ['foo', 'baz']);
    }
}
