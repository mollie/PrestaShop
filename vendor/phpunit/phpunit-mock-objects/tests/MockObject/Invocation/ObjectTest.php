<?php

namespace MolliePrefix;

class Framework_MockObject_Invocation_ObjectTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testConstructorRequiresClassAndMethodAndParametersAndObject()
    {
        $this->assertInstanceOf(\MolliePrefix\PHPUnit_Framework_MockObject_Invocation_Object::class, new \MolliePrefix\PHPUnit_Framework_MockObject_Invocation_Object('FooClass', 'FooMethod', ['an_argument'], 'ReturnType', new \stdClass()));
    }
    public function testAllowToGetClassNameSetInConstructor()
    {
        $invocation = new \MolliePrefix\PHPUnit_Framework_MockObject_Invocation_Object('FooClass', 'FooMethod', ['an_argument'], 'ReturnType', new \stdClass());
        $this->assertSame('FooClass', $invocation->className);
    }
    public function testAllowToGetMethodNameSetInConstructor()
    {
        $invocation = new \MolliePrefix\PHPUnit_Framework_MockObject_Invocation_Object('FooClass', 'FooMethod', ['an_argument'], 'ReturnType', new \stdClass());
        $this->assertSame('FooMethod', $invocation->methodName);
    }
    public function testAllowToGetObjectSetInConstructor()
    {
        $expectedObject = new \stdClass();
        $invocation = new \MolliePrefix\PHPUnit_Framework_MockObject_Invocation_Object('FooClass', 'FooMethod', ['an_argument'], 'ReturnType', $expectedObject);
        $this->assertSame($expectedObject, $invocation->object);
    }
    public function testAllowToGetMethodParametersSetInConstructor()
    {
        $expectedParameters = ['foo', 5, ['a', 'b'], new \stdClass(), null, \false];
        $invocation = new \MolliePrefix\PHPUnit_Framework_MockObject_Invocation_Object('FooClass', 'FooMethod', $expectedParameters, 'ReturnType', new \stdClass());
        $this->assertSame($expectedParameters, $invocation->parameters);
    }
    public function testConstructorAllowToSetFlagCloneObjectsInParameters()
    {
        $parameters = [new \stdClass()];
        $cloneObjects = \true;
        $invocation = new \MolliePrefix\PHPUnit_Framework_MockObject_Invocation_Object('FooClass', 'FooMethod', $parameters, 'ReturnType', new \stdClass(), $cloneObjects);
        $this->assertEquals($parameters, $invocation->parameters);
        $this->assertNotSame($parameters, $invocation->parameters);
    }
    public function testAllowToGetReturnTypeSetInConstructor()
    {
        $expectedReturnType = 'string';
        $invocation = new \MolliePrefix\PHPUnit_Framework_MockObject_Invocation_Object('FooClass', 'FooMethod', ['an_argument'], $expectedReturnType, new \stdClass());
        $this->assertSame($expectedReturnType, $invocation->returnType);
    }
}
\class_alias('MolliePrefix\\Framework_MockObject_Invocation_ObjectTest', 'MolliePrefix\\Framework_MockObject_Invocation_ObjectTest', \false);
