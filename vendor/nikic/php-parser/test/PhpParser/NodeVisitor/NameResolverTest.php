<?php

namespace MolliePrefix\PhpParser\NodeVisitor;

use MolliePrefix\PhpParser;
use MolliePrefix\PhpParser\Node;
use MolliePrefix\PhpParser\Node\Expr;
use MolliePrefix\PhpParser\Node\Name;
use MolliePrefix\PhpParser\Node\Stmt;
class NameResolverTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    private function canonicalize($string)
    {
        return \str_replace("\r\n", "\n", $string);
    }
    /**
     * @covers PhpParser\NodeVisitor\NameResolver
     */
    public function testResolveNames()
    {
        $code = <<<'EOC'
<?php

namespace MolliePrefix\Foo;

use MolliePrefix\Hallo as Hi;
new \MolliePrefix\Foo\Bar();
new \MolliePrefix\Hallo();
new \MolliePrefix\Hallo\Bar();
new \MolliePrefix\Bar();
new \MolliePrefix\Foo\Bar();
bar();
hi();
\MolliePrefix\Hallo\bar();
\MolliePrefix\Foo\foo\bar();
\MolliePrefix\bar();
namespace\bar();
namespace MolliePrefix;

use MolliePrefix\Hallo as Hi;
new \MolliePrefix\Bar();
new \MolliePrefix\Hallo();
new \MolliePrefix\Hallo\Bar();
new \MolliePrefix\Bar();
new \MolliePrefix\Bar();
\MolliePrefix\bar();
\MolliePrefix\hi();
\MolliePrefix\Hallo\bar();
\MolliePrefix\foo\bar();
\MolliePrefix\bar();
\MolliePrefix\bar();
namespace MolliePrefix\Bar;

use function MolliePrefix\foo\bar as baz;
use const MolliePrefix\foo\BAR as BAZ;
use MolliePrefix\foo as bar;
bar();
\MolliePrefix\foo\bar();
\MolliePrefix\foo\foo();
\MolliePrefix\foo\bar\foo();
BAR();
\MolliePrefix\foo\bar();
\MolliePrefix\foo\FOO();
\MolliePrefix\foo\bar\FOO();
bar;
\MolliePrefix\foo\BAR;
\MolliePrefix\foo\foo;
\MolliePrefix\foo\bar\foo;
BAR;
\MolliePrefix\foo\BAR;
\MolliePrefix\foo\FOO;
\MolliePrefix\foo\bar\FOO;
namespace MolliePrefix\Baz;

use MolliePrefix\A\T\B\C;
use MolliePrefix\A\T\D\E;
use function MolliePrefix\X\T\b\c;
use function MolliePrefix\X\T\d\e;
use const MolliePrefix\Y\T\B\C;
use const MolliePrefix\Y\T\D\E;
use MolliePrefix\Z\T\G;
use function MolliePrefix\Z\T\f;
use const MolliePrefix\Z\T\K;
new \MolliePrefix\A\T\B\C();
new \MolliePrefix\A\T\D\E();
new \MolliePrefix\A\T\B\C\D();
new \MolliePrefix\A\T\D\E\F();
new \MolliePrefix\Z\T\G();
\MolliePrefix\X\T\b\c();
\MolliePrefix\X\T\d\e();
f();
\MolliePrefix\Y\T\B\C;
\MolliePrefix\Y\T\D\E;
K;
EOC;
        $expectedCode = <<<'EOC'
namespace Foo {
    use Hallo as Hi;
    new \Foo\Bar();
    new \Hallo();
    new \Hallo\Bar();
    new \Bar();
    new \Foo\Bar();
    bar();
    hi();
    \Hallo\bar();
    \Foo\foo\bar();
    \bar();
    \Foo\bar();
}
namespace {
    use Hallo as Hi;
    new \Bar();
    new \Hallo();
    new \Hallo\Bar();
    new \Bar();
    new \Bar();
    \bar();
    \hi();
    \Hallo\bar();
    \foo\bar();
    \bar();
    \bar();
}
namespace Bar {
    use function foo\bar as baz;
    use const foo\BAR as BAZ;
    use foo as bar;
    bar();
    \foo\bar();
    \foo\foo();
    \Bar\baz\foo();
    BAR();
    \foo\bar();
    \foo\FOO();
    \Bar\BAZ\FOO();
    bar;
    baz;
    \foo\foo;
    \Bar\baz\foo;
    BAR;
    \foo\BAR;
    \foo\FOO;
    \Bar\BAZ\FOO;
}
namespace Baz {
    use A\T\{B\C, D\E};
    use function X\T\{b\c, d\e};
    use const Y\T\{B\C, D\E};
    use Z\T\{G, function f, const K};
    new \A\T\B\C();
    new \A\T\D\E();
    new \A\T\B\C\D();
    new \A\T\D\E\F();
    new \Z\T\G();
    \X\T\b\c();
    \X\T\d\e();
    \Z\T\f();
    \Y\T\B\C;
    \Y\T\D\E;
    \Z\T\K;
}
EOC;
        $parser = new \MolliePrefix\PhpParser\Parser\Php7(new \MolliePrefix\PhpParser\Lexer\Emulative());
        $prettyPrinter = new \MolliePrefix\PhpParser\PrettyPrinter\Standard();
        $traverser = new \MolliePrefix\PhpParser\NodeTraverser();
        $traverser->addVisitor(new \MolliePrefix\PhpParser\NodeVisitor\NameResolver());
        $stmts = $parser->parse($code);
        $stmts = $traverser->traverse($stmts);
        $this->assertSame($this->canonicalize($expectedCode), $prettyPrinter->prettyPrint($stmts));
    }
    /**
     * @covers PhpParser\NodeVisitor\NameResolver
     */
    public function testResolveLocations()
    {
        $code = <<<'EOC'
<?php
namespace NS;

class A extends B implements C, D {
    use E, F, G {
        f as private g;
        E::h as i;
        E::j insteadof F, G;
    }
}

interface A extends C, D {
    public function a(A $a) : A;
}

function fn(A $a) : A {}
function fn2(array $a) : array {}
function(A $a) : A {};

function fn3(?A $a) : ?A {}
function fn4(?array $a) : ?array {}

A::b();
A::$b;
A::B;
new A;
$a instanceof A;

namespace\a();
namespace\A;

try {
    $someThing;
} catch (A $a) {
    $someThingElse;
}
EOC;
        $expectedCode = <<<'EOC'
namespace NS;

class A extends \NS\B implements \NS\C, \NS\D
{
    use \NS\E, \NS\F, \NS\G {
        f as private g;
        \NS\E::h as i;
        \NS\E::j insteadof \NS\F, \NS\G;
    }
}
interface A extends \NS\C, \NS\D
{
    public function a(\NS\A $a) : \NS\A;
}
function fn(\NS\A $a) : \NS\A
{
}
function fn2(array $a) : array
{
}
function (\NS\A $a) : \NS\A {
};
function fn3(?\NS\A $a) : ?\NS\A
{
}
function fn4(?array $a) : ?array
{
}
\NS\A::b();
\NS\A::$b;
\NS\A::B;
new \NS\A();
$a instanceof \NS\A;
\NS\a();
\NS\A;
try {
    $someThing;
} catch (\NS\A $a) {
    $someThingElse;
}
EOC;
        $parser = new \MolliePrefix\PhpParser\Parser\Php7(new \MolliePrefix\PhpParser\Lexer\Emulative());
        $prettyPrinter = new \MolliePrefix\PhpParser\PrettyPrinter\Standard();
        $traverser = new \MolliePrefix\PhpParser\NodeTraverser();
        $traverser->addVisitor(new \MolliePrefix\PhpParser\NodeVisitor\NameResolver());
        $stmts = $parser->parse($code);
        $stmts = $traverser->traverse($stmts);
        $this->assertSame($this->canonicalize($expectedCode), $prettyPrinter->prettyPrint($stmts));
    }
    public function testNoResolveSpecialName()
    {
        $stmts = array(new \MolliePrefix\PhpParser\Node\Expr\New_(new \MolliePrefix\PhpParser\Node\Name('self')));
        $traverser = new \MolliePrefix\PhpParser\NodeTraverser();
        $traverser->addVisitor(new \MolliePrefix\PhpParser\NodeVisitor\NameResolver());
        $this->assertEquals($stmts, $traverser->traverse($stmts));
    }
    public function testAddDeclarationNamespacedName()
    {
        $nsStmts = array(new \MolliePrefix\PhpParser\Node\Stmt\Class_('A'), new \MolliePrefix\PhpParser\Node\Stmt\Interface_('B'), new \MolliePrefix\PhpParser\Node\Stmt\Function_('C'), new \MolliePrefix\PhpParser\Node\Stmt\Const_(array(new \MolliePrefix\PhpParser\Node\Const_('D', new \MolliePrefix\PhpParser\Node\Scalar\LNumber(42)))), new \MolliePrefix\PhpParser\Node\Stmt\Trait_('E'), new \MolliePrefix\PhpParser\Node\Expr\New_(new \MolliePrefix\PhpParser\Node\Stmt\Class_(null)));
        $traverser = new \MolliePrefix\PhpParser\NodeTraverser();
        $traverser->addVisitor(new \MolliePrefix\PhpParser\NodeVisitor\NameResolver());
        $stmts = $traverser->traverse([new \MolliePrefix\PhpParser\Node\Stmt\Namespace_(new \MolliePrefix\PhpParser\Node\Name('NS'), $nsStmts)]);
        $this->assertSame('MolliePrefix\\NS\\A', (string) $stmts[0]->stmts[0]->namespacedName);
        $this->assertSame('MolliePrefix\\NS\\B', (string) $stmts[0]->stmts[1]->namespacedName);
        $this->assertSame('MolliePrefix\\NS\\C', (string) $stmts[0]->stmts[2]->namespacedName);
        $this->assertSame('MolliePrefix\\NS\\D', (string) $stmts[0]->stmts[3]->consts[0]->namespacedName);
        $this->assertSame('MolliePrefix\\NS\\E', (string) $stmts[0]->stmts[4]->namespacedName);
        $this->assertObjectNotHasAttribute('namespacedName', $stmts[0]->stmts[5]->class);
        $stmts = $traverser->traverse([new \MolliePrefix\PhpParser\Node\Stmt\Namespace_(null, $nsStmts)]);
        $this->assertSame('A', (string) $stmts[0]->stmts[0]->namespacedName);
        $this->assertSame('B', (string) $stmts[0]->stmts[1]->namespacedName);
        $this->assertSame('C', (string) $stmts[0]->stmts[2]->namespacedName);
        $this->assertSame('D', (string) $stmts[0]->stmts[3]->consts[0]->namespacedName);
        $this->assertSame('E', (string) $stmts[0]->stmts[4]->namespacedName);
        $this->assertObjectNotHasAttribute('namespacedName', $stmts[0]->stmts[5]->class);
    }
    public function testAddRuntimeResolvedNamespacedName()
    {
        $stmts = array(new \MolliePrefix\PhpParser\Node\Stmt\Namespace_(new \MolliePrefix\PhpParser\Node\Name('NS'), array(new \MolliePrefix\PhpParser\Node\Expr\FuncCall(new \MolliePrefix\PhpParser\Node\Name('foo')), new \MolliePrefix\PhpParser\Node\Expr\ConstFetch(new \MolliePrefix\PhpParser\Node\Name('FOO')))), new \MolliePrefix\PhpParser\Node\Stmt\Namespace_(null, array(new \MolliePrefix\PhpParser\Node\Expr\FuncCall(new \MolliePrefix\PhpParser\Node\Name('foo')), new \MolliePrefix\PhpParser\Node\Expr\ConstFetch(new \MolliePrefix\PhpParser\Node\Name('FOO')))));
        $traverser = new \MolliePrefix\PhpParser\NodeTraverser();
        $traverser->addVisitor(new \MolliePrefix\PhpParser\NodeVisitor\NameResolver());
        $stmts = $traverser->traverse($stmts);
        $this->assertSame('MolliePrefix\\NS\\foo', (string) $stmts[0]->stmts[0]->name->getAttribute('namespacedName'));
        $this->assertSame('MolliePrefix\\NS\\FOO', (string) $stmts[0]->stmts[1]->name->getAttribute('namespacedName'));
        $this->assertFalse($stmts[1]->stmts[0]->name->hasAttribute('namespacedName'));
        $this->assertFalse($stmts[1]->stmts[1]->name->hasAttribute('namespacedName'));
    }
    /**
     * @dataProvider provideTestError
     */
    public function testError(\MolliePrefix\PhpParser\Node $stmt, $errorMsg)
    {
        $this->setExpectedException('MolliePrefix\\PhpParser\\Error', $errorMsg);
        $traverser = new \MolliePrefix\PhpParser\NodeTraverser();
        $traverser->addVisitor(new \MolliePrefix\PhpParser\NodeVisitor\NameResolver());
        $traverser->traverse(array($stmt));
    }
    public function provideTestError()
    {
        return array(array(new \MolliePrefix\PhpParser\Node\Stmt\Use_(array(new \MolliePrefix\PhpParser\Node\Stmt\UseUse(new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\A\\B'), 'B', 0, array('startLine' => 1)), new \MolliePrefix\PhpParser\Node\Stmt\UseUse(new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\C\\D'), 'B', 0, array('startLine' => 2))), \MolliePrefix\PhpParser\Node\Stmt\Use_::TYPE_NORMAL), 'Cannot use C\\D as B because the name is already in use on line 2'), array(new \MolliePrefix\PhpParser\Node\Stmt\Use_(array(new \MolliePrefix\PhpParser\Node\Stmt\UseUse(new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\a\\b'), 'b', 0, array('startLine' => 1)), new \MolliePrefix\PhpParser\Node\Stmt\UseUse(new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\c\\d'), 'B', 0, array('startLine' => 2))), \MolliePrefix\PhpParser\Node\Stmt\Use_::TYPE_FUNCTION), 'Cannot use function c\\d as B because the name is already in use on line 2'), array(new \MolliePrefix\PhpParser\Node\Stmt\Use_(array(new \MolliePrefix\PhpParser\Node\Stmt\UseUse(new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\A\\B'), 'B', 0, array('startLine' => 1)), new \MolliePrefix\PhpParser\Node\Stmt\UseUse(new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\C\\D'), 'B', 0, array('startLine' => 2))), \MolliePrefix\PhpParser\Node\Stmt\Use_::TYPE_CONSTANT), 'Cannot use const C\\D as B because the name is already in use on line 2'), array(new \MolliePrefix\PhpParser\Node\Expr\New_(new \MolliePrefix\PhpParser\Node\Name\FullyQualified('self', array('startLine' => 3))), "'\\self' is an invalid class name on line 3"), array(new \MolliePrefix\PhpParser\Node\Expr\New_(new \MolliePrefix\PhpParser\Node\Name\Relative('self', array('startLine' => 3))), "'\\self' is an invalid class name on line 3"), array(new \MolliePrefix\PhpParser\Node\Expr\New_(new \MolliePrefix\PhpParser\Node\Name\FullyQualified('PARENT', array('startLine' => 3))), "'\\PARENT' is an invalid class name on line 3"), array(new \MolliePrefix\PhpParser\Node\Expr\New_(new \MolliePrefix\PhpParser\Node\Name\Relative('STATIC', array('startLine' => 3))), "'\\STATIC' is an invalid class name on line 3"));
    }
    public function testClassNameIsCaseInsensitive()
    {
        $source = <<<'EOC'
<?php

namespace MolliePrefix\Foo;

use MolliePrefix\Bar\Baz;
$test = new \MolliePrefix\Bar\Baz();
EOC;
        $parser = new \MolliePrefix\PhpParser\Parser\Php7(new \MolliePrefix\PhpParser\Lexer\Emulative());
        $stmts = $parser->parse($source);
        $traverser = new \MolliePrefix\PhpParser\NodeTraverser();
        $traverser->addVisitor(new \MolliePrefix\PhpParser\NodeVisitor\NameResolver());
        $stmts = $traverser->traverse($stmts);
        $stmt = $stmts[0];
        $this->assertSame(array('Bar', 'Baz'), $stmt->stmts[1]->expr->class->parts);
    }
    public function testSpecialClassNamesAreCaseInsensitive()
    {
        $source = <<<'EOC'
<?php

namespace MolliePrefix\Foo;

class Bar
{
    public static function method()
    {
        \MolliePrefix\Foo\SELF::method();
        \MolliePrefix\Foo\PARENT::method();
        \MolliePrefix\Foo\STATIC::method();
    }
}
EOC;
        $parser = new \MolliePrefix\PhpParser\Parser\Php7(new \MolliePrefix\PhpParser\Lexer\Emulative());
        $stmts = $parser->parse($source);
        $traverser = new \MolliePrefix\PhpParser\NodeTraverser();
        $traverser->addVisitor(new \MolliePrefix\PhpParser\NodeVisitor\NameResolver());
        $stmts = $traverser->traverse($stmts);
        $classStmt = $stmts[0];
        $methodStmt = $classStmt->stmts[0]->stmts[0];
        $this->assertSame('SELF', (string) $methodStmt->stmts[0]->class);
        $this->assertSame('PARENT', (string) $methodStmt->stmts[1]->class);
        $this->assertSame('STATIC', (string) $methodStmt->stmts[2]->class);
    }
    public function testAddOriginalNames()
    {
        $traverser = new \MolliePrefix\PhpParser\NodeTraverser();
        $traverser->addVisitor(new \MolliePrefix\PhpParser\NodeVisitor\NameResolver(null, ['preserveOriginalNames' => \true]));
        $n1 = new \MolliePrefix\PhpParser\Node\Name('Bar');
        $n2 = new \MolliePrefix\PhpParser\Node\Name('bar');
        $origStmts = [new \MolliePrefix\PhpParser\Node\Stmt\Namespace_(new \MolliePrefix\PhpParser\Node\Name('Foo'), [new \MolliePrefix\PhpParser\Node\Expr\ClassConstFetch($n1, 'FOO'), new \MolliePrefix\PhpParser\Node\Expr\FuncCall($n2)])];
        $stmts = $traverser->traverse($origStmts);
        $this->assertSame($n1, $stmts[0]->stmts[0]->class->getAttribute('originalName'));
        $this->assertSame($n2, $stmts[0]->stmts[1]->name->getAttribute('originalName'));
    }
}
