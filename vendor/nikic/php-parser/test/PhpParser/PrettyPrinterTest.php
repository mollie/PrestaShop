<?php

namespace MolliePrefix\PhpParser;

use MolliePrefix\PhpParser\Comment;
use MolliePrefix\PhpParser\Node\Expr;
use MolliePrefix\PhpParser\Node\Name;
use MolliePrefix\PhpParser\Node\Scalar\DNumber;
use MolliePrefix\PhpParser\Node\Scalar\Encapsed;
use MolliePrefix\PhpParser\Node\Scalar\EncapsedStringPart;
use MolliePrefix\PhpParser\Node\Scalar\LNumber;
use MolliePrefix\PhpParser\Node\Scalar\String_;
use MolliePrefix\PhpParser\Node\Stmt;
use MolliePrefix\PhpParser\PrettyPrinter\Standard;
require_once __DIR__ . '/CodeTestAbstract.php';
class PrettyPrinterTest extends \MolliePrefix\PhpParser\CodeTestAbstract
{
    protected function doTestPrettyPrintMethod($method, $name, $code, $expected, $modeLine)
    {
        $lexer = new \MolliePrefix\PhpParser\Lexer\Emulative();
        $parser5 = new \MolliePrefix\PhpParser\Parser\Php5($lexer);
        $parser7 = new \MolliePrefix\PhpParser\Parser\Php7($lexer);
        list($version, $options) = $this->parseModeLine($modeLine);
        $prettyPrinter = new \MolliePrefix\PhpParser\PrettyPrinter\Standard($options);
        try {
            $output5 = canonicalize($prettyPrinter->{$method}($parser5->parse($code)));
        } catch (\MolliePrefix\PhpParser\Error $e) {
            $output5 = null;
            if ('php7' !== $version) {
                throw $e;
            }
        }
        try {
            $output7 = canonicalize($prettyPrinter->{$method}($parser7->parse($code)));
        } catch (\MolliePrefix\PhpParser\Error $e) {
            $output7 = null;
            if ('php5' !== $version) {
                throw $e;
            }
        }
        if ('php5' === $version) {
            $this->assertSame($expected, $output5, $name);
            $this->assertNotSame($expected, $output7, $name);
        } else {
            if ('php7' === $version) {
                $this->assertSame($expected, $output7, $name);
                $this->assertNotSame($expected, $output5, $name);
            } else {
                $this->assertSame($expected, $output5, $name);
                $this->assertSame($expected, $output7, $name);
            }
        }
    }
    /**
     * @dataProvider provideTestPrettyPrint
     * @covers PhpParser\PrettyPrinter\Standard<extended>
     */
    public function testPrettyPrint($name, $code, $expected, $mode)
    {
        $this->doTestPrettyPrintMethod('prettyPrint', $name, $code, $expected, $mode);
    }
    /**
     * @dataProvider provideTestPrettyPrintFile
     * @covers PhpParser\PrettyPrinter\Standard<extended>
     */
    public function testPrettyPrintFile($name, $code, $expected, $mode)
    {
        $this->doTestPrettyPrintMethod('prettyPrintFile', $name, $code, $expected, $mode);
    }
    public function provideTestPrettyPrint()
    {
        return $this->getTests(__DIR__ . '/../code/prettyPrinter', 'test');
    }
    public function provideTestPrettyPrintFile()
    {
        return $this->getTests(__DIR__ . '/../code/prettyPrinter', 'file-test');
    }
    public function testPrettyPrintExpr()
    {
        $prettyPrinter = new \MolliePrefix\PhpParser\PrettyPrinter\Standard();
        $expr = new \MolliePrefix\PhpParser\Node\Expr\BinaryOp\Mul(new \MolliePrefix\PhpParser\Node\Expr\BinaryOp\Plus(new \MolliePrefix\PhpParser\Node\Expr\Variable('a'), new \MolliePrefix\PhpParser\Node\Expr\Variable('b')), new \MolliePrefix\PhpParser\Node\Expr\Variable('c'));
        $this->assertEquals('($a + $b) * $c', $prettyPrinter->prettyPrintExpr($expr));
        $expr = new \MolliePrefix\PhpParser\Node\Expr\Closure(array('stmts' => array(new \MolliePrefix\PhpParser\Node\Stmt\Return_(new \MolliePrefix\PhpParser\Node\Scalar\String_("a\nb")))));
        $this->assertEquals("function () {\n    return 'a\nb';\n}", $prettyPrinter->prettyPrintExpr($expr));
    }
    public function testCommentBeforeInlineHTML()
    {
        $prettyPrinter = new \MolliePrefix\PhpParser\PrettyPrinter\Standard();
        $comment = new \MolliePrefix\PhpParser\Comment\Doc("/**\n * This is a comment\n */");
        $stmts = [new \MolliePrefix\PhpParser\Node\Stmt\InlineHTML('Hello World!', ['comments' => [$comment]])];
        $expected = "<?php\n\n/**\n * This is a comment\n */\n?>\nHello World!";
        $this->assertSame($expected, $prettyPrinter->prettyPrintFile($stmts));
    }
    private function parseModeLine($modeLine)
    {
        $parts = \explode(' ', $modeLine, 2);
        $version = isset($parts[0]) ? $parts[0] : 'both';
        $options = isset($parts[1]) ? \json_decode($parts[1], \true) : [];
        return [$version, $options];
    }
    public function testArraySyntaxDefault()
    {
        $prettyPrinter = new \MolliePrefix\PhpParser\PrettyPrinter\Standard(['shortArraySyntax' => \true]);
        $expr = new \MolliePrefix\PhpParser\Node\Expr\Array_([new \MolliePrefix\PhpParser\Node\Expr\ArrayItem(new \MolliePrefix\PhpParser\Node\Scalar\String_('val'), new \MolliePrefix\PhpParser\Node\Scalar\String_('key'))]);
        $expected = "['key' => 'val']";
        $this->assertSame($expected, $prettyPrinter->prettyPrintExpr($expr));
    }
    /**
     * @dataProvider provideTestKindAttributes
     */
    public function testKindAttributes($node, $expected)
    {
        $prttyPrinter = new \MolliePrefix\PhpParser\PrettyPrinter\Standard();
        $result = $prttyPrinter->prettyPrintExpr($node);
        $this->assertSame($expected, $result);
    }
    public function provideTestKindAttributes()
    {
        $nowdoc = ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_NOWDOC, 'docLabel' => 'STR'];
        $heredoc = ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_HEREDOC, 'docLabel' => 'STR'];
        return [
            // Defaults to single quoted
            [new \MolliePrefix\PhpParser\Node\Scalar\String_('foo'), "'foo'"],
            // Explicit single/double quoted
            [new \MolliePrefix\PhpParser\Node\Scalar\String_('foo', ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_SINGLE_QUOTED]), "'foo'"],
            [new \MolliePrefix\PhpParser\Node\Scalar\String_('foo', ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_DOUBLE_QUOTED]), '"foo"'],
            // Fallback from doc string if no label
            [new \MolliePrefix\PhpParser\Node\Scalar\String_('foo', ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_NOWDOC]), "'foo'"],
            [new \MolliePrefix\PhpParser\Node\Scalar\String_('foo', ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_HEREDOC]), '"foo"'],
            // Fallback if string contains label
            [new \MolliePrefix\PhpParser\Node\Scalar\String_("A\nB\nC", ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_NOWDOC, 'docLabel' => 'A']), "'A\nB\nC'"],
            [new \MolliePrefix\PhpParser\Node\Scalar\String_("A\nB\nC", ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_NOWDOC, 'docLabel' => 'B']), "'A\nB\nC'"],
            [new \MolliePrefix\PhpParser\Node\Scalar\String_("A\nB\nC", ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_NOWDOC, 'docLabel' => 'C']), "'A\nB\nC'"],
            [new \MolliePrefix\PhpParser\Node\Scalar\String_("STR;", ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_NOWDOC, 'docLabel' => 'STR']), "'STR;'"],
            // Doc string if label not contained (or not in ending position)
            [new \MolliePrefix\PhpParser\Node\Scalar\String_("foo", $nowdoc), "<<<'STR'\nfoo\nSTR\n"],
            [new \MolliePrefix\PhpParser\Node\Scalar\String_("foo", $heredoc), "<<<STR\nfoo\nSTR\n"],
            [new \MolliePrefix\PhpParser\Node\Scalar\String_("STRx", $nowdoc), "<<<'STR'\nSTRx\nSTR\n"],
            [new \MolliePrefix\PhpParser\Node\Scalar\String_("xSTR", $nowdoc), "<<<'STR'\nxSTR\nSTR\n"],
            // Empty doc string variations (encapsed variant does not occur naturally)
            [new \MolliePrefix\PhpParser\Node\Scalar\String_("", $nowdoc), "<<<'STR'\nSTR\n"],
            [new \MolliePrefix\PhpParser\Node\Scalar\String_("", $heredoc), "<<<STR\nSTR\n"],
            [new \MolliePrefix\PhpParser\Node\Scalar\Encapsed([new \MolliePrefix\PhpParser\Node\Scalar\EncapsedStringPart('')], $heredoc), "<<<STR\nSTR\n"],
            // Encapsed doc string variations
            [new \MolliePrefix\PhpParser\Node\Scalar\Encapsed([new \MolliePrefix\PhpParser\Node\Scalar\EncapsedStringPart('foo')], $heredoc), "<<<STR\nfoo\nSTR\n"],
            [new \MolliePrefix\PhpParser\Node\Scalar\Encapsed([new \MolliePrefix\PhpParser\Node\Scalar\EncapsedStringPart('foo'), new \MolliePrefix\PhpParser\Node\Expr\Variable('y')], $heredoc), "<<<STR\nfoo{\$y}\nSTR\n"],
            [new \MolliePrefix\PhpParser\Node\Scalar\Encapsed([new \MolliePrefix\PhpParser\Node\Scalar\EncapsedStringPart("\nSTR"), new \MolliePrefix\PhpParser\Node\Expr\Variable('y')], $heredoc), "<<<STR\n\nSTR{\$y}\nSTR\n"],
            [new \MolliePrefix\PhpParser\Node\Scalar\Encapsed([new \MolliePrefix\PhpParser\Node\Scalar\EncapsedStringPart("\nSTR"), new \MolliePrefix\PhpParser\Node\Expr\Variable('y')], $heredoc), "<<<STR\n\nSTR{\$y}\nSTR\n"],
            [new \MolliePrefix\PhpParser\Node\Scalar\Encapsed([new \MolliePrefix\PhpParser\Node\Expr\Variable('y'), new \MolliePrefix\PhpParser\Node\Scalar\EncapsedStringPart("STR\n")], $heredoc), "<<<STR\n{\$y}STR\n\nSTR\n"],
            // Encapsed doc string fallback
            [new \MolliePrefix\PhpParser\Node\Scalar\Encapsed([new \MolliePrefix\PhpParser\Node\Expr\Variable('y'), new \MolliePrefix\PhpParser\Node\Scalar\EncapsedStringPart("\nSTR")], $heredoc), '"{$y}\\nSTR"'],
            [new \MolliePrefix\PhpParser\Node\Scalar\Encapsed([new \MolliePrefix\PhpParser\Node\Scalar\EncapsedStringPart("STR\n"), new \MolliePrefix\PhpParser\Node\Expr\Variable('y')], $heredoc), '"STR\\n{$y}"'],
            [new \MolliePrefix\PhpParser\Node\Scalar\Encapsed([new \MolliePrefix\PhpParser\Node\Scalar\EncapsedStringPart("STR")], $heredoc), '"STR"'],
        ];
    }
    /** @dataProvider provideTestUnnaturalLiterals */
    public function testUnnaturalLiterals($node, $expected)
    {
        $prttyPrinter = new \MolliePrefix\PhpParser\PrettyPrinter\Standard();
        $result = $prttyPrinter->prettyPrintExpr($node);
        $this->assertSame($expected, $result);
    }
    public function provideTestUnnaturalLiterals()
    {
        return [[new \MolliePrefix\PhpParser\Node\Scalar\LNumber(-1), '-1'], [new \MolliePrefix\PhpParser\Node\Scalar\LNumber(-\PHP_INT_MAX - 1), '(-' . \PHP_INT_MAX . '-1)'], [new \MolliePrefix\PhpParser\Node\Scalar\LNumber(-1, ['kind' => \MolliePrefix\PhpParser\Node\Scalar\LNumber::KIND_BIN]), '-0b1'], [new \MolliePrefix\PhpParser\Node\Scalar\LNumber(-1, ['kind' => \MolliePrefix\PhpParser\Node\Scalar\LNumber::KIND_OCT]), '-01'], [new \MolliePrefix\PhpParser\Node\Scalar\LNumber(-1, ['kind' => \MolliePrefix\PhpParser\Node\Scalar\LNumber::KIND_HEX]), '-0x1'], [new \MolliePrefix\PhpParser\Node\Scalar\DNumber(\INF), '\\INF'], [new \MolliePrefix\PhpParser\Node\Scalar\DNumber(-\INF), '-\\INF'], [new \MolliePrefix\PhpParser\Node\Scalar\DNumber(-\NAN), '\\NAN']];
    }
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Cannot pretty-print AST with Error nodes
     */
    public function testPrettyPrintWithError()
    {
        $stmts = [new \MolliePrefix\PhpParser\Node\Expr\PropertyFetch(new \MolliePrefix\PhpParser\Node\Expr\Variable('a'), new \MolliePrefix\PhpParser\Node\Expr\Error())];
        $prettyPrinter = new \MolliePrefix\PhpParser\PrettyPrinter\Standard();
        $prettyPrinter->prettyPrint($stmts);
    }
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Cannot pretty-print AST with Error nodes
     */
    public function testPrettyPrintWithErrorInClassConstFetch()
    {
        $stmts = [new \MolliePrefix\PhpParser\Node\Expr\ClassConstFetch(new \MolliePrefix\PhpParser\Node\Name('Foo'), new \MolliePrefix\PhpParser\Node\Expr\Error())];
        $prettyPrinter = new \MolliePrefix\PhpParser\PrettyPrinter\Standard();
        $prettyPrinter->prettyPrint($stmts);
    }
}
