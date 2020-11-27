<?php

namespace MolliePrefix\PhpParser\Builder;

use MolliePrefix\PhpParser\Node;
use MolliePrefix\PhpParser\Node\Expr;
use MolliePrefix\PhpParser\Node\Scalar;
class ParamTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function createParamBuilder($name)
    {
        return new \MolliePrefix\PhpParser\Builder\Param($name);
    }
    /**
     * @dataProvider provideTestDefaultValues
     */
    public function testDefaultValues($value, $expectedValueNode)
    {
        $node = $this->createParamBuilder('test')->setDefault($value)->getNode();
        $this->assertEquals($expectedValueNode, $node->default);
    }
    public function provideTestDefaultValues()
    {
        return array(array(null, new \MolliePrefix\PhpParser\Node\Expr\ConstFetch(new \MolliePrefix\PhpParser\Node\Name('null'))), array(\true, new \MolliePrefix\PhpParser\Node\Expr\ConstFetch(new \MolliePrefix\PhpParser\Node\Name('true'))), array(\false, new \MolliePrefix\PhpParser\Node\Expr\ConstFetch(new \MolliePrefix\PhpParser\Node\Name('false'))), array(31415, new \MolliePrefix\PhpParser\Node\Scalar\LNumber(31415)), array(3.1415, new \MolliePrefix\PhpParser\Node\Scalar\DNumber(3.1415)), array('Hallo World', new \MolliePrefix\PhpParser\Node\Scalar\String_('Hallo World')), array(array(1, 2, 3), new \MolliePrefix\PhpParser\Node\Expr\Array_(array(new \MolliePrefix\PhpParser\Node\Expr\ArrayItem(new \MolliePrefix\PhpParser\Node\Scalar\LNumber(1)), new \MolliePrefix\PhpParser\Node\Expr\ArrayItem(new \MolliePrefix\PhpParser\Node\Scalar\LNumber(2)), new \MolliePrefix\PhpParser\Node\Expr\ArrayItem(new \MolliePrefix\PhpParser\Node\Scalar\LNumber(3))))), array(array('foo' => 'bar', 'bar' => 'foo'), new \MolliePrefix\PhpParser\Node\Expr\Array_(array(new \MolliePrefix\PhpParser\Node\Expr\ArrayItem(new \MolliePrefix\PhpParser\Node\Scalar\String_('bar'), new \MolliePrefix\PhpParser\Node\Scalar\String_('foo')), new \MolliePrefix\PhpParser\Node\Expr\ArrayItem(new \MolliePrefix\PhpParser\Node\Scalar\String_('foo'), new \MolliePrefix\PhpParser\Node\Scalar\String_('bar'))))), array(new \MolliePrefix\PhpParser\Node\Scalar\MagicConst\Dir(), new \MolliePrefix\PhpParser\Node\Scalar\MagicConst\Dir()));
    }
    /**
     * @dataProvider provideTestTypeHints
     */
    public function testTypeHints($typeHint, $expectedType)
    {
        $node = $this->createParamBuilder('test')->setTypeHint($typeHint)->getNode();
        $type = $node->type;
        /* Manually implement comparison to avoid __toString stupidity */
        if ($expectedType instanceof \MolliePrefix\PhpParser\Node\NullableType) {
            $this->assertInstanceOf(\get_class($expectedType), $type);
            $expectedType = $expectedType->type;
            $type = $type->type;
        }
        if ($expectedType instanceof \MolliePrefix\PhpParser\Node\Name) {
            $this->assertInstanceOf(\get_class($expectedType), $type);
            $this->assertEquals($expectedType, $type);
        } else {
            $this->assertSame($expectedType, $type);
        }
    }
    public function provideTestTypeHints()
    {
        return array(array('array', 'array'), array('callable', 'callable'), array('bool', 'bool'), array('int', 'int'), array('float', 'float'), array('string', 'string'), array('iterable', 'iterable'), array('object', 'object'), array('Array', 'array'), array('CALLABLE', 'callable'), array('MolliePrefix\\Some\\Class', new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\Some\\Class')), array('\\Foo', new \MolliePrefix\PhpParser\Node\Name\FullyQualified('Foo')), array('self', new \MolliePrefix\PhpParser\Node\Name('self')), array('?array', new \MolliePrefix\PhpParser\Node\NullableType('array')), array('MolliePrefix\\?Some\\Class', new \MolliePrefix\PhpParser\Node\NullableType(new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\Some\\Class'))), array(new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\Some\\Class'), new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\Some\\Class')), array(new \MolliePrefix\PhpParser\Node\NullableType('int'), new \MolliePrefix\PhpParser\Node\NullableType('int')), array(new \MolliePrefix\PhpParser\Node\NullableType(new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\Some\\Class')), new \MolliePrefix\PhpParser\Node\NullableType(new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\Some\\Class'))));
    }
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Parameter type cannot be void
     */
    public function testVoidTypeError()
    {
        $this->createParamBuilder('test')->setTypeHint('void');
    }
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Type must be a string, or an instance of Name or NullableType
     */
    public function testInvalidTypeError()
    {
        $this->createParamBuilder('test')->setTypeHint(new \stdClass());
    }
    public function testByRef()
    {
        $node = $this->createParamBuilder('test')->makeByRef()->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Param('test', null, null, \true), $node);
    }
    public function testVariadic()
    {
        $node = $this->createParamBuilder('test')->makeVariadic()->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Param('test', null, null, \false, \true), $node);
    }
}
