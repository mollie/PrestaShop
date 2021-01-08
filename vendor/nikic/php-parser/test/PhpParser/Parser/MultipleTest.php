<?php

namespace MolliePrefix\PhpParser\Parser;

use MolliePrefix\PhpParser\Error;
use MolliePrefix\PhpParser\Lexer;
use MolliePrefix\PhpParser\Node\Expr;
use MolliePrefix\PhpParser\Node\Scalar\LNumber;
use MolliePrefix\PhpParser\Node\Stmt;
use MolliePrefix\PhpParser\ParserTest;
require_once __DIR__ . '/../ParserTest.php';
class MultipleTest extends \MolliePrefix\PhpParser\ParserTest
{
    // This provider is for the generic parser tests, just pick an arbitrary order here
    protected function getParser(\MolliePrefix\PhpParser\Lexer $lexer)
    {
        return new \MolliePrefix\PhpParser\Parser\Multiple([new \MolliePrefix\PhpParser\Parser\Php5($lexer), new \MolliePrefix\PhpParser\Parser\Php7($lexer)]);
    }
    private function getPrefer7()
    {
        $lexer = new \MolliePrefix\PhpParser\Lexer(['usedAttributes' => []]);
        return new \MolliePrefix\PhpParser\Parser\Multiple([new \MolliePrefix\PhpParser\Parser\Php7($lexer), new \MolliePrefix\PhpParser\Parser\Php5($lexer)]);
    }
    private function getPrefer5()
    {
        $lexer = new \MolliePrefix\PhpParser\Lexer(['usedAttributes' => []]);
        return new \MolliePrefix\PhpParser\Parser\Multiple([new \MolliePrefix\PhpParser\Parser\Php5($lexer), new \MolliePrefix\PhpParser\Parser\Php7($lexer)]);
    }
    /** @dataProvider provideTestParse */
    public function testParse($code, \MolliePrefix\PhpParser\Parser\Multiple $parser, $expected)
    {
        $this->assertEquals($expected, $parser->parse($code));
    }
    public function provideTestParse()
    {
        return [[
            // PHP 7 only code
            '<?php class Test { function function() {} }',
            $this->getPrefer5(),
            [new \MolliePrefix\PhpParser\Node\Stmt\Class_('Test', ['stmts' => [new \MolliePrefix\PhpParser\Node\Stmt\ClassMethod('function')]])],
        ], [
            // PHP 5 only code
            '<?php global $$a->b;',
            $this->getPrefer7(),
            [new \MolliePrefix\PhpParser\Node\Stmt\Global_([new \MolliePrefix\PhpParser\Node\Expr\Variable(new \MolliePrefix\PhpParser\Node\Expr\PropertyFetch(new \MolliePrefix\PhpParser\Node\Expr\Variable('a'), 'b'))])],
        ], [
            // Different meaning (PHP 5)
            '<?php $$a[0];',
            $this->getPrefer5(),
            [new \MolliePrefix\PhpParser\Node\Expr\Variable(new \MolliePrefix\PhpParser\Node\Expr\ArrayDimFetch(new \MolliePrefix\PhpParser\Node\Expr\Variable('a'), \MolliePrefix\PhpParser\Node\Scalar\LNumber::fromString('0')))],
        ], [
            // Different meaning (PHP 7)
            '<?php $$a[0];',
            $this->getPrefer7(),
            [new \MolliePrefix\PhpParser\Node\Expr\ArrayDimFetch(new \MolliePrefix\PhpParser\Node\Expr\Variable(new \MolliePrefix\PhpParser\Node\Expr\Variable('a')), \MolliePrefix\PhpParser\Node\Scalar\LNumber::fromString('0'))],
        ]];
    }
    public function testThrownError()
    {
        $this->setExpectedException('MolliePrefix\\PhpParser\\Error', 'FAIL A');
        $parserA = $this->getMockBuilder('MolliePrefix\\PhpParser\\Parser')->getMock();
        $parserA->expects($this->at(0))->method('parse')->will($this->throwException(new \MolliePrefix\PhpParser\Error('FAIL A')));
        $parserB = $this->getMockBuilder('MolliePrefix\\PhpParser\\Parser')->getMock();
        $parserB->expects($this->at(0))->method('parse')->will($this->throwException(new \MolliePrefix\PhpParser\Error('FAIL B')));
        $parser = new \MolliePrefix\PhpParser\Parser\Multiple([$parserA, $parserB]);
        $parser->parse('dummy');
    }
}
