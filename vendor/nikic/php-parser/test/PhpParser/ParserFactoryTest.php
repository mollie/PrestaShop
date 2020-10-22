<?php

namespace MolliePrefix\PhpParser;

/* This test is very weak, because PHPUnit's assertEquals assertion is way too slow dealing with the
 * large objects involved here. So we just do some basic instanceof tests instead. */
class ParserFactoryTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /** @dataProvider provideTestCreate */
    public function testCreate($kind, $lexer, $expected)
    {
        $this->assertInstanceOf($expected, (new \MolliePrefix\PhpParser\ParserFactory())->create($kind, $lexer));
    }
    public function provideTestCreate()
    {
        $lexer = new \MolliePrefix\PhpParser\Lexer();
        return [[\MolliePrefix\PhpParser\ParserFactory::PREFER_PHP7, $lexer, 'MolliePrefix\\PhpParser\\Parser\\Multiple'], [\MolliePrefix\PhpParser\ParserFactory::PREFER_PHP5, null, 'MolliePrefix\\PhpParser\\Parser\\Multiple'], [\MolliePrefix\PhpParser\ParserFactory::ONLY_PHP7, null, 'MolliePrefix\\PhpParser\\Parser\\Php7'], [\MolliePrefix\PhpParser\ParserFactory::ONLY_PHP5, $lexer, 'MolliePrefix\\PhpParser\\Parser\\Php5']];
    }
}
