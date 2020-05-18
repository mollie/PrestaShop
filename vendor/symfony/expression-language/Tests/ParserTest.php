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
use _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Lexer;
use _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node;
use _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ArgumentsNode;
use _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ArrayNode;
use _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\BinaryNode;
use _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConditionalNode;
use _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\ConstantNode;
use _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\GetAttrNode;
use _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\NameNode;
use _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\UnaryNode;
use _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Parser;
class ParserTest extends TestCase
{
    public function testParseWithInvalidName()
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\ExpressionLanguage\\SyntaxError');
        $this->expectExceptionMessage('Variable "foo" is not valid around position 1 for expression `foo`.');
        $lexer = new Lexer();
        $parser = new Parser([]);
        $parser->parse($lexer->tokenize('foo'));
    }
    public function testParseWithZeroInNames()
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\ExpressionLanguage\\SyntaxError');
        $this->expectExceptionMessage('Variable "foo" is not valid around position 1 for expression `foo`.');
        $lexer = new Lexer();
        $parser = new Parser([]);
        $parser->parse($lexer->tokenize('foo'), [0]);
    }
    /**
     * @dataProvider getParseData
     */
    public function testParse($node, $expression, $names = [])
    {
        $lexer = new Lexer();
        $parser = new Parser([]);
        $this->assertEquals($node, $parser->parse($lexer->tokenize($expression), $names));
    }
    public function getParseData()
    {
        $arguments = new ArgumentsNode();
        $arguments->addElement(new ConstantNode('arg1'));
        $arguments->addElement(new ConstantNode(2));
        $arguments->addElement(new ConstantNode(true));
        $arrayNode = new ArrayNode();
        $arrayNode->addElement(new NameNode('bar'));
        return [
            [new NameNode('a'), 'a', ['a']],
            [new ConstantNode('a'), '"a"'],
            [new ConstantNode(3), '3'],
            [new ConstantNode(false), 'false'],
            [new ConstantNode(true), 'true'],
            [new ConstantNode(null), 'null'],
            [new UnaryNode('-', new ConstantNode(3)), '-3'],
            [new BinaryNode('-', new ConstantNode(3), new ConstantNode(3)), '3 - 3'],
            [new BinaryNode('*', new BinaryNode('-', new ConstantNode(3), new ConstantNode(3)), new ConstantNode(2)), '(3 - 3) * 2'],
            [new GetAttrNode(new NameNode('foo'), new ConstantNode('bar', true), new ArgumentsNode(), GetAttrNode::PROPERTY_CALL), 'foo.bar', ['foo']],
            [new GetAttrNode(new NameNode('foo'), new ConstantNode('bar', true), new ArgumentsNode(), GetAttrNode::METHOD_CALL), 'foo.bar()', ['foo']],
            [new GetAttrNode(new NameNode('foo'), new ConstantNode('not', true), new ArgumentsNode(), GetAttrNode::METHOD_CALL), 'foo.not()', ['foo']],
            [new GetAttrNode(new NameNode('foo'), new ConstantNode('bar', true), $arguments, GetAttrNode::METHOD_CALL), 'foo.bar("arg1", 2, true)', ['foo']],
            [new GetAttrNode(new NameNode('foo'), new ConstantNode(3), new ArgumentsNode(), GetAttrNode::ARRAY_CALL), 'foo[3]', ['foo']],
            [new ConditionalNode(new ConstantNode(true), new ConstantNode(true), new ConstantNode(false)), 'true ? true : false'],
            [new BinaryNode('matches', new ConstantNode('foo'), new ConstantNode('/foo/')), '"foo" matches "/foo/"'],
            // chained calls
            [$this->createGetAttrNode($this->createGetAttrNode($this->createGetAttrNode($this->createGetAttrNode(new NameNode('foo'), 'bar', GetAttrNode::METHOD_CALL), 'foo', GetAttrNode::METHOD_CALL), 'baz', GetAttrNode::PROPERTY_CALL), '3', GetAttrNode::ARRAY_CALL), 'foo.bar().foo().baz[3]', ['foo']],
            [new NameNode('foo'), 'bar', ['foo' => 'bar']],
            // Operators collisions
            [new BinaryNode('in', new GetAttrNode(new NameNode('foo'), new ConstantNode('not', true), new ArgumentsNode(), GetAttrNode::PROPERTY_CALL), $arrayNode), 'foo.not in [bar]', ['foo', 'bar']],
            [new BinaryNode('or', new UnaryNode('not', new NameNode('foo')), new GetAttrNode(new NameNode('foo'), new ConstantNode('not', true), new ArgumentsNode(), GetAttrNode::PROPERTY_CALL)), 'not foo or foo.not', ['foo']],
        ];
    }
    private function createGetAttrNode($node, $item, $type)
    {
        return new GetAttrNode($node, new ConstantNode($item, GetAttrNode::ARRAY_CALL !== $type), new ArgumentsNode(), $type);
    }
    /**
     * @dataProvider getInvalidPostfixData
     */
    public function testParseWithInvalidPostfixData($expr, $names = [])
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\ExpressionLanguage\\SyntaxError');
        $lexer = new Lexer();
        $parser = new Parser([]);
        $parser->parse($lexer->tokenize($expr), $names);
    }
    public function getInvalidPostfixData()
    {
        return [['foo."#"', ['foo']], ['foo."bar"', ['foo']], ['foo.**', ['foo']], ['foo.123', ['foo']]];
    }
    public function testNameProposal()
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\ExpressionLanguage\\SyntaxError');
        $this->expectExceptionMessage('Did you mean "baz"?');
        $lexer = new Lexer();
        $parser = new Parser([]);
        $parser->parse($lexer->tokenize('foo > bar'), ['foo', 'baz']);
    }
}
