<?php

namespace MolliePrefix;

class Framework_MockObject_Invocation_StaticTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testConstructorRequiresClassAndMethodAndParameters()
    {
        $this->assertInstanceOf(\MolliePrefix\PHPUnit_Framework_MockObject_Invocation_Static::class, new \MolliePrefix\PHPUnit_Framework_MockObject_Invocation_Static('FooClass', 'FooMethod', ['an_argument'], 'ReturnType'));
    }
    public function testAllowToGetClassNameSetInConstructor()
    {
        $invocation = new \MolliePrefix\PHPUnit_Framework_MockObject_Invocation_Static('FooClass', 'FooMethod', ['an_argument'], 'ReturnType');
        $this->assertSame('FooClass', $invocation->className);
    }
    public function testAllowToGetMethodNameSetInConstructor()
    {
        $invocation = new \MolliePrefix\PHPUnit_Framework_MockObject_Invocation_Static('FooClass', 'FooMethod', ['an_argument'], 'ReturnType');
        $this->assertSame('FooMethod', $invocation->methodName);
    }
    public function testAllowToGetMethodParametersSetInConstructor()
    {
        $expectedParameters = ['foo', 5, ['a', 'b'], new \stdClass(), null, \false];
        $invocation = new \MolliePrefix\PHPUnit_Framework_MockObject_Invocation_Static('FooClass', 'FooMethod', $expectedParameters, 'ReturnType');
        $this->assertSame($expectedParameters, $invocation->parameters);
    }
    public function testConstructorAllowToSetFlagCloneObjectsInParameters()
    {
        $parameters = [new \stdClass()];
        $cloneObjects = \true;
        $invocation = new \MolliePrefix\PHPUnit_Framework_MockObject_Invocation_Static('FooClass', 'FooMethod', $parameters, 'ReturnType', $cloneObjects);
        $this->assertEquals($parameters, $invocation->parameters);
        $this->assertNotSame($parameters, $invocation->parameters);
    }
    public function testAllowToGetReturnTypeSetInConstructor()
    {
        $expectedReturnType = 'string';
        $invocation = new \MolliePrefix\PHPUnit_Framework_MockObject_Invocation_Static('FooClass', 'FooMethod', ['an_argument'], $expectedReturnType);
        $this->assertSame($expectedReturnType, $invocation->returnType);
    }
}
\class_alias('MolliePrefix\\Framework_MockObject_Invocation_StaticTest', 'MolliePrefix\\Framework_MockObject_Invocation_StaticTest', \false);
