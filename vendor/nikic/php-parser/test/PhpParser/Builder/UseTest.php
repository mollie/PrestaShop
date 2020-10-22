<?php

namespace MolliePrefix;

use MolliePrefix\PhpParser\Builder;
use MolliePrefix\PhpParser\Node\Name;
use MolliePrefix\PhpParser\Node\Stmt;
class UseTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    protected function createUseBuilder($name, $type = \MolliePrefix\PhpParser\Node\Stmt\Use_::TYPE_NORMAL)
    {
        return new \MolliePrefix\PhpParser\Builder\Use_($name, $type);
    }
    public function testCreation()
    {
        $node = $this->createUseBuilder('MolliePrefix\\Foo\\Bar')->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\Use_(array(new \MolliePrefix\PhpParser\Node\Stmt\UseUse(new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\Foo\\Bar'), 'Bar'))), $node);
        $node = $this->createUseBuilder(new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\Foo\\Bar'))->as('XYZ')->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\Use_(array(new \MolliePrefix\PhpParser\Node\Stmt\UseUse(new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\Foo\\Bar'), 'XYZ'))), $node);
        $node = $this->createUseBuilder('MolliePrefix\\foo\\bar', \MolliePrefix\PhpParser\Node\Stmt\Use_::TYPE_FUNCTION)->as('foo')->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\Use_(array(new \MolliePrefix\PhpParser\Node\Stmt\UseUse(new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\foo\\bar'), 'foo')), \MolliePrefix\PhpParser\Node\Stmt\Use_::TYPE_FUNCTION), $node);
    }
    public function testNonExistingMethod()
    {
        $this->setExpectedException('LogicException', 'Method "foo" does not exist');
        $builder = $this->createUseBuilder('Test');
        $builder->foo();
    }
}
\class_alias('MolliePrefix\\UseTest', 'MolliePrefix\\UseTest', \false);
