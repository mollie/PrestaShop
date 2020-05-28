<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Tests;

use _PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase;
use _PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Lexer;
use _PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token;
use _PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\TokenStream;
class LexerTest extends \_PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase
{
    /**
     * @var Lexer
     */
    private $lexer;
    protected function setUp()
    {
        $this->lexer = new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Lexer();
    }
    /**
     * @dataProvider getTokenizeData
     */
    public function testTokenize($tokens, $expression)
    {
        $tokens[] = new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('end of expression', null, \strlen($expression) + 1);
        $this->assertEquals(new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\TokenStream($tokens, $expression), $this->lexer->tokenize($expression));
    }
    public function testTokenizeThrowsErrorWithMessage()
    {
        $this->expectException('_PhpScoper5ece82d7231e4\\Symfony\\Component\\ExpressionLanguage\\SyntaxError');
        $this->expectExceptionMessage('Unexpected character "\'" around position 33 for expression `service(faulty.expression.example\').dummyMethod()`.');
        $expression = "service(faulty.expression.example').dummyMethod()";
        $this->lexer->tokenize($expression);
    }
    public function testTokenizeThrowsErrorOnUnclosedBrace()
    {
        $this->expectException('_PhpScoper5ece82d7231e4\\Symfony\\Component\\ExpressionLanguage\\SyntaxError');
        $this->expectExceptionMessage('Unclosed "(" around position 7 for expression `service(unclosed.expression.dummyMethod()`.');
        $expression = 'service(unclosed.expression.dummyMethod()';
        $this->lexer->tokenize($expression);
    }
    public function getTokenizeData()
    {
        return [[[new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('name', 'a', 3)], '  a  '], [[new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('name', 'a', 1)], 'a'], [[new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('string', 'foo', 1)], '"foo"'], [[new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('number', '3', 1)], '3'], [[new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('operator', '+', 1)], '+'], [[new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('punctuation', '.', 1)], '.'], [[new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('punctuation', '(', 1), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('number', '3', 2), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('operator', '+', 4), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('number', '5', 6), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('punctuation', ')', 7), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('operator', '~', 9), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('name', 'foo', 11), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('punctuation', '(', 14), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('string', 'bar', 15), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('punctuation', ')', 20), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('punctuation', '.', 21), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('name', 'baz', 22), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('punctuation', '[', 25), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('number', '4', 26), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('punctuation', ']', 27)], '(3 + 5) ~ foo("bar").baz[4]'], [[new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('operator', '..', 1)], '..'], [[new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('string', '#foo', 1)], "'#foo'"], [[new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('string', '#foo', 1)], '"#foo"'], [[new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('name', 'foo', 1), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('punctuation', '.', 4), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('name', 'not', 5), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('operator', 'in', 9), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('punctuation', '[', 12), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('name', 'bar', 13), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Token('punctuation', ']', 16)], 'foo.not in [bar]']];
    }
}
