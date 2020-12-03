<?php

namespace MolliePrefix\PhpParser;

use MolliePrefix\PhpParser\Node\Expr;
class BuilderFactoryTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideTestFactory
     */
    public function testFactory($methodName, $className)
    {
        $factory = new \MolliePrefix\PhpParser\BuilderFactory();
        $this->assertInstanceOf($className, $factory->{$methodName}('test'));
    }
    public function provideTestFactory()
    {
        return array(array('namespace', 'MolliePrefix\\PhpParser\\Builder\\Namespace_'), array('class', 'MolliePrefix\\PhpParser\\Builder\\Class_'), array('interface', 'MolliePrefix\\PhpParser\\Builder\\Interface_'), array('trait', 'MolliePrefix\\PhpParser\\Builder\\Trait_'), array('method', 'MolliePrefix\\PhpParser\\Builder\\Method'), array('function', 'MolliePrefix\\PhpParser\\Builder\\Function_'), array('property', 'MolliePrefix\\PhpParser\\Builder\\Property'), array('param', 'MolliePrefix\\PhpParser\\Builder\\Param'), array('use', 'MolliePrefix\\PhpParser\\Builder\\Use_'));
    }
    public function testNonExistingMethod()
    {
        $this->setExpectedException('LogicException', 'Method "foo" does not exist');
        $factory = new \MolliePrefix\PhpParser\BuilderFactory();
        $factory->foo();
    }
    public function testIntegration()
    {
        $factory = new \MolliePrefix\PhpParser\BuilderFactory();
        $node = $factory->namespace('MolliePrefix\\Name\\Space')->addStmt($factory->use('MolliePrefix\\Foo\\Bar\\SomeOtherClass'))->addStmt($factory->use('MolliePrefix\\Foo\\Bar')->as('A'))->addStmt($factory->class('SomeClass')->extend('SomeOtherClass')->implement('MolliePrefix\\A\\Few', '\\Interfaces')->makeAbstract()->addStmt($factory->method('firstMethod'))->addStmt($factory->method('someMethod')->makePublic()->makeAbstract()->addParam($factory->param('someParam')->setTypeHint('SomeClass'))->setDocComment('/**
                                      * This method does something.
                                      *
                                      * @param SomeClass And takes a parameter
                                      */'))->addStmt($factory->method('anotherMethod')->makeProtected()->addParam($factory->param('someParam')->setDefault('test'))->addStmt(new \MolliePrefix\PhpParser\Node\Expr\Print_(new \MolliePrefix\PhpParser\Node\Expr\Variable('someParam'))))->addStmt($factory->property('someProperty')->makeProtected())->addStmt($factory->property('anotherProperty')->makePrivate()->setDefault(array(1, 2, 3))))->getNode();
        $expected = <<<'EOC'
<?php

namespace MolliePrefix\Name\Space;

use MolliePrefix\Foo\Bar\SomeOtherClass;
use MolliePrefix\Foo\Bar as A;
abstract class SomeClass extends \MolliePrefix\Foo\Bar\SomeOtherClass implements \MolliePrefix\Foo\Bar\Few, \MolliePrefix\Interfaces
{
    protected $someProperty;
    private $anotherProperty = array(1, 2, 3);
    function firstMethod()
    {
    }
    /**
     * This method does something.
     *
     * @param SomeClass And takes a parameter
     */
    public abstract function someMethod(\MolliePrefix\Name\Space\SomeClass $someParam);
    protected function anotherMethod($someParam = 'test')
    {
        print $someParam;
    }
}
EOC;
        $stmts = array($node);
        $prettyPrinter = new \MolliePrefix\PhpParser\PrettyPrinter\Standard();
        $generated = $prettyPrinter->prettyPrintFile($stmts);
        $this->assertEquals(\str_replace("\r\n", "\n", $expected), \str_replace("\r\n", "\n", $generated));
    }
}
