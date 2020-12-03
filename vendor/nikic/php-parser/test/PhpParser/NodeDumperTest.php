<?php

namespace MolliePrefix\PhpParser;

class NodeDumperTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    private function canonicalize($string)
    {
        return \str_replace("\r\n", "\n", $string);
    }
    /**
     * @dataProvider provideTestDump
     */
    public function testDump($node, $dump)
    {
        $dumper = new \MolliePrefix\PhpParser\NodeDumper();
        $this->assertSame($this->canonicalize($dump), $this->canonicalize($dumper->dump($node)));
    }
    public function provideTestDump()
    {
        return array(array(array(), 'array(
)'), array(array('Foo', 'Bar', 'Key' => 'FooBar'), 'array(
    0: Foo
    1: Bar
    Key: FooBar
)'), array(new \MolliePrefix\PhpParser\Node\Name(array('Hallo', 'World')), 'Name(
    parts: array(
        0: Hallo
        1: World
    )
)'), array(new \MolliePrefix\PhpParser\Node\Expr\Array_(array(new \MolliePrefix\PhpParser\Node\Expr\ArrayItem(new \MolliePrefix\PhpParser\Node\Scalar\String_('Foo')))), 'Expr_Array(
    items: array(
        0: Expr_ArrayItem(
            key: null
            value: Scalar_String(
                value: Foo
            )
            byRef: false
        )
    )
)'));
    }
    public function testDumpWithPositions()
    {
        $parser = (new \MolliePrefix\PhpParser\ParserFactory())->create(\MolliePrefix\PhpParser\ParserFactory::ONLY_PHP7, new \MolliePrefix\PhpParser\Lexer(['usedAttributes' => ['startLine', 'endLine', 'startFilePos', 'endFilePos']]));
        $dumper = new \MolliePrefix\PhpParser\NodeDumper(['dumpPositions' => \true]);
        $code = "<?php\n\$a = 1;\necho \$a;";
        $expected = <<<'OUT'
array(
    0: Expr_Assign[2:1 - 2:6](
        var: Expr_Variable[2:1 - 2:2](
            name: a
        )
        expr: Scalar_LNumber[2:6 - 2:6](
            value: 1
        )
    )
    1: Stmt_Echo[3:1 - 3:8](
        exprs: array(
            0: Expr_Variable[3:6 - 3:7](
                name: a
            )
        )
    )
)
OUT;
        $stmts = $parser->parse($code);
        $dump = $dumper->dump($stmts, $code);
        $this->assertSame($this->canonicalize($expected), $this->canonicalize($dump));
    }
    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Can only dump nodes and arrays.
     */
    public function testError()
    {
        $dumper = new \MolliePrefix\PhpParser\NodeDumper();
        $dumper->dump(new \stdClass());
    }
}
