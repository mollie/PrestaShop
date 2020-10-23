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
use MolliePrefix\Symfony\Component\ExpressionLanguage\Lexer;
use MolliePrefix\Symfony\Component\ExpressionLanguage\Node;
use MolliePrefix\Symfony\Component\ExpressionLanguage\Parser;
class ParserTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testParseWithInvalidName()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\ExpressionLanguage\\SyntaxError');
        $this->expectExceptionMessage('Variable "foo" is not valid around position 1 for expression `foo`.');
        $lexer = new \MolliePrefix\Symfony\Component\ExpressionLanguage\Lexer();
        $parser = new \MolliePrefix\Symfony\Component\ExpressionLanguage\Parser([]);
        $parser->parse($lexer->tokenize('foo'));
    }
    public function testParseWithZeroInNames()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\ExpressionLanguage\\SyntaxError');
        $this->expectExceptionMessage('Variable "foo" is not valid around position 1 for expression `foo`.');
        $lexer = new \MolliePrefix\Symfony\Component\ExpressionLanguage\Lexer();
        $parser = new \MolliePrefix\Symfony\Component\ExpressionLanguage\Parser([]);
        $parser->parse($lexer->tokenize('foo'), [0]);
    }
    /**
     * @dataProvider getParseData
     */
    public function testParse($node, $expression, $names = [])
    {
        $lexer = new \MolliePrefix\Symfony\Component\ExpressionLanguage\Lexer();
        $parser = new \MolliePrefix\Symfony\Component\ExpressionLanguage\Parser([]);
        $this->assertEquals($node, $parser->parse($lexer->tokenize($expression), $names));
    }
    public function getParseData()
    {
        $arguments = new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ArgumentsNode();
        $arguments->addElement(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode('arg1'));
        $arguments->addElement(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(2));
        $arguments->addElement(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true));
        $arrayNode = new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ArrayNode();
        $arrayNode->addElement(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('bar'));
        return [
            [new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('a'), 'a', ['a']],
            [new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode('a'), '"a"'],
            [new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(3), '3'],
            [new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\false), 'false'],
            [new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true), 'true'],
            [new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(null), 'null'],
            [new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\UnaryNode('-', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(3)), '-3'],
            [new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\BinaryNode('-', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(3), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(3)), '3 - 3'],
            [new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\BinaryNode('*', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\BinaryNode('-', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(3), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(3)), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(2)), '(3 - 3) * 2'],
            [new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode('bar', \true), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ArgumentsNode(), \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::PROPERTY_CALL), 'foo.bar', ['foo']],
            [new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode('bar', \true), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ArgumentsNode(), \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::METHOD_CALL), 'foo.bar()', ['foo']],
            [new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode('not', \true), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ArgumentsNode(), \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::METHOD_CALL), 'foo.not()', ['foo']],
            [new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode('bar', \true), $arguments, \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::METHOD_CALL), 'foo.bar("arg1", 2, true)', ['foo']],
            [new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(3), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ArgumentsNode(), \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::ARRAY_CALL), 'foo[3]', ['foo']],
            [new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConditionalNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\true), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(\false)), 'true ? true : false'],
            [new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\BinaryNode('matches', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode('foo'), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode('/foo/')), '"foo" matches "/foo/"'],
            // chained calls
            [$this->createGetAttrNode($this->createGetAttrNode($this->createGetAttrNode($this->createGetAttrNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), 'bar', \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::METHOD_CALL), 'foo', \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::METHOD_CALL), 'baz', \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::PROPERTY_CALL), '3', \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::ARRAY_CALL), 'foo.bar().foo().baz[3]', ['foo']],
            [new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), 'bar', ['foo' => 'bar']],
            // Operators collisions
            [new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\BinaryNode('in', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode('not', \true), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ArgumentsNode(), \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::PROPERTY_CALL), $arrayNode), 'foo.not in [bar]', ['foo', 'bar']],
            [new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\BinaryNode('or', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\UnaryNode('not', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo')), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode('not', \true), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ArgumentsNode(), \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::PROPERTY_CALL)), 'not foo or foo.not', ['foo']],
        ];
    }
    private function createGetAttrNode($node, $item, $type)
    {
        return new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode($node, new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode($item, \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::ARRAY_CALL !== $type), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ArgumentsNode(), $type);
    }
    /**
     * @dataProvider getInvalidPostfixData
     */
    public function testParseWithInvalidPostfixData($expr, $names = [])
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\ExpressionLanguage\\SyntaxError');
        $lexer = new \MolliePrefix\Symfony\Component\ExpressionLanguage\Lexer();
        $parser = new \MolliePrefix\Symfony\Component\ExpressionLanguage\Parser([]);
        $parser->parse($lexer->tokenize($expr), $names);
    }
    public function getInvalidPostfixData()
    {
        return [['foo."#"', ['foo']], ['foo."bar"', ['foo']], ['foo.**', ['foo']], ['foo.123', ['foo']]];
    }
    public function testNameProposal()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\ExpressionLanguage\\SyntaxError');
        $this->expectExceptionMessage('Did you mean "baz"?');
        $lexer = new \MolliePrefix\Symfony\Component\ExpressionLanguage\Lexer();
        $parser = new \MolliePrefix\Symfony\Component\ExpressionLanguage\Parser([]);
        $parser->parse($lexer->tokenize('foo > bar'), ['foo', 'baz']);
    }
}
