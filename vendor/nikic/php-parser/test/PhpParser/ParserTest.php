<?php

namespace MolliePrefix\PhpParser;

use MolliePrefix\PhpParser\Comment;
use MolliePrefix\PhpParser\Node\Expr;
use MolliePrefix\PhpParser\Node\Scalar;
use MolliePrefix\PhpParser\Node\Scalar\String_;
abstract class ParserTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /** @returns Parser */
    protected abstract function getParser(\MolliePrefix\PhpParser\Lexer $lexer);
    /**
     * @expectedException \PhpParser\Error
     * @expectedExceptionMessage Syntax error, unexpected EOF on line 1
     */
    public function testParserThrowsSyntaxError()
    {
        $parser = $this->getParser(new \MolliePrefix\PhpParser\Lexer());
        $parser->parse('<?php foo');
    }
    /**
     * @expectedException \PhpParser\Error
     * @expectedExceptionMessage Cannot use foo as self because 'self' is a special class name on line 1
     */
    public function testParserThrowsSpecialError()
    {
        $parser = $this->getParser(new \MolliePrefix\PhpParser\Lexer());
        $parser->parse('<?php use foo as self;');
    }
    /**
     * @expectedException \PhpParser\Error
     * @expectedExceptionMessage Unterminated comment on line 1
     */
    public function testParserThrowsLexerError()
    {
        $parser = $this->getParser(new \MolliePrefix\PhpParser\Lexer());
        $parser->parse('<?php /*');
    }
    public function testAttributeAssignment()
    {
        $lexer = new \MolliePrefix\PhpParser\Lexer(array('usedAttributes' => array('comments', 'startLine', 'endLine', 'startTokenPos', 'endTokenPos')));
        $code = <<<'EOC'
<?php

namespace MolliePrefix;

/** Doc comment */
function test($a)
{
    // Line
    // Comments
    echo $a;
}
EOC;
        $code = canonicalize($code);
        $parser = $this->getParser($lexer);
        $stmts = $parser->parse($code);
        /** @var \PhpParser\Node\Stmt\Function_ $fn */
        $fn = $stmts[0];
        $this->assertInstanceOf('MolliePrefix\\PhpParser\\Node\\Stmt\\Function_', $fn);
        $this->assertEquals(array('comments' => array(new \MolliePrefix\PhpParser\Comment\Doc('/** Doc comment */', 2, 6)), 'startLine' => 3, 'endLine' => 7, 'startTokenPos' => 3, 'endTokenPos' => 21), $fn->getAttributes());
        $param = $fn->params[0];
        $this->assertInstanceOf('MolliePrefix\\PhpParser\\Node\\Param', $param);
        $this->assertEquals(array('startLine' => 3, 'endLine' => 3, 'startTokenPos' => 7, 'endTokenPos' => 7), $param->getAttributes());
        /** @var \PhpParser\Node\Stmt\Echo_ $echo */
        $echo = $fn->stmts[0];
        $this->assertInstanceOf('MolliePrefix\\PhpParser\\Node\\Stmt\\Echo_', $echo);
        $this->assertEquals(array('comments' => array(new \MolliePrefix\PhpParser\Comment("// Line\n", 4, 49), new \MolliePrefix\PhpParser\Comment("// Comments\n", 5, 61)), 'startLine' => 6, 'endLine' => 6, 'startTokenPos' => 16, 'endTokenPos' => 19), $echo->getAttributes());
        /** @var \PhpParser\Node\Expr\Variable $var */
        $var = $echo->exprs[0];
        $this->assertInstanceOf('MolliePrefix\\PhpParser\\Node\\Expr\\Variable', $var);
        $this->assertEquals(array('startLine' => 6, 'endLine' => 6, 'startTokenPos' => 18, 'endTokenPos' => 18), $var->getAttributes());
    }
    /**
     * @expectedException \RangeException
     * @expectedExceptionMessage The lexer returned an invalid token (id=999, value=foobar)
     */
    public function testInvalidToken()
    {
        $lexer = new \MolliePrefix\PhpParser\InvalidTokenLexer();
        $parser = $this->getParser($lexer);
        $parser->parse('dummy');
    }
    /**
     * @dataProvider provideTestExtraAttributes
     */
    public function testExtraAttributes($code, $expectedAttributes)
    {
        $parser = $this->getParser(new \MolliePrefix\PhpParser\Lexer());
        $stmts = $parser->parse("<?php {$code};");
        $attributes = $stmts[0]->getAttributes();
        foreach ($expectedAttributes as $name => $value) {
            $this->assertSame($value, $attributes[$name]);
        }
    }
    public function provideTestExtraAttributes()
    {
        return array(
            array('0', ['kind' => \MolliePrefix\PhpParser\Node\Scalar\LNumber::KIND_DEC]),
            array('9', ['kind' => \MolliePrefix\PhpParser\Node\Scalar\LNumber::KIND_DEC]),
            array('07', ['kind' => \MolliePrefix\PhpParser\Node\Scalar\LNumber::KIND_OCT]),
            array('0xf', ['kind' => \MolliePrefix\PhpParser\Node\Scalar\LNumber::KIND_HEX]),
            array('0XF', ['kind' => \MolliePrefix\PhpParser\Node\Scalar\LNumber::KIND_HEX]),
            array('0b1', ['kind' => \MolliePrefix\PhpParser\Node\Scalar\LNumber::KIND_BIN]),
            array('0B1', ['kind' => \MolliePrefix\PhpParser\Node\Scalar\LNumber::KIND_BIN]),
            array('[]', ['kind' => \MolliePrefix\PhpParser\Node\Expr\Array_::KIND_SHORT]),
            array('array()', ['kind' => \MolliePrefix\PhpParser\Node\Expr\Array_::KIND_LONG]),
            array("'foo'", ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_SINGLE_QUOTED]),
            array("b'foo'", ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_SINGLE_QUOTED]),
            array("B'foo'", ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_SINGLE_QUOTED]),
            array('"foo"', ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_DOUBLE_QUOTED]),
            array('b"foo"', ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_DOUBLE_QUOTED]),
            array('B"foo"', ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_DOUBLE_QUOTED]),
            array('"foo$bar"', ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_DOUBLE_QUOTED]),
            array('b"foo$bar"', ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_DOUBLE_QUOTED]),
            array('B"foo$bar"', ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_DOUBLE_QUOTED]),
            array("<<<'STR'\nSTR\n", ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_NOWDOC, 'docLabel' => 'STR']),
            array("<<<STR\nSTR\n", ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_HEREDOC, 'docLabel' => 'STR']),
            array("<<<\"STR\"\nSTR\n", ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_HEREDOC, 'docLabel' => 'STR']),
            array("b<<<'STR'\nSTR\n", ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_NOWDOC, 'docLabel' => 'STR']),
            array("B<<<'STR'\nSTR\n", ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_NOWDOC, 'docLabel' => 'STR']),
            array("<<< \t 'STR'\nSTR\n", ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_NOWDOC, 'docLabel' => 'STR']),
            // HHVM doesn't support this due to a lexer bug
            // (https://github.com/facebook/hhvm/issues/6970)
            // array("<<<'\xff'\n\xff\n", ['kind' => String_::KIND_NOWDOC, 'docLabel' => "\xff"]),
            array("<<<\"STR\"\n\$a\nSTR\n", ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_HEREDOC, 'docLabel' => 'STR']),
            array("b<<<\"STR\"\n\$a\nSTR\n", ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_HEREDOC, 'docLabel' => 'STR']),
            array("B<<<\"STR\"\n\$a\nSTR\n", ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_HEREDOC, 'docLabel' => 'STR']),
            array("<<< \t \"STR\"\n\$a\nSTR\n", ['kind' => \MolliePrefix\PhpParser\Node\Scalar\String_::KIND_HEREDOC, 'docLabel' => 'STR']),
            array("die", ['kind' => \MolliePrefix\PhpParser\Node\Expr\Exit_::KIND_DIE]),
            array("die('done')", ['kind' => \MolliePrefix\PhpParser\Node\Expr\Exit_::KIND_DIE]),
            array("exit", ['kind' => \MolliePrefix\PhpParser\Node\Expr\Exit_::KIND_EXIT]),
            array("exit(1)", ['kind' => \MolliePrefix\PhpParser\Node\Expr\Exit_::KIND_EXIT]),
            array("?>Foo", ['hasLeadingNewline' => \false]),
            array("?>\nFoo", ['hasLeadingNewline' => \true]),
            array("namespace Foo;", ['kind' => \MolliePrefix\PhpParser\Node\Stmt\Namespace_::KIND_SEMICOLON]),
            array("namespace Foo {}", ['kind' => \MolliePrefix\PhpParser\Node\Stmt\Namespace_::KIND_BRACED]),
            array("namespace {}", ['kind' => \MolliePrefix\PhpParser\Node\Stmt\Namespace_::KIND_BRACED]),
        );
    }
}
class InvalidTokenLexer extends \MolliePrefix\PhpParser\Lexer
{
    public function getNextToken(&$value = null, &$startAttributes = null, &$endAttributes = null)
    {
        $value = 'foobar';
        return 999;
    }
}
