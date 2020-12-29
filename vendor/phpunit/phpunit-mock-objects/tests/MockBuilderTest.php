<?php

namespace MolliePrefix;

/*
 * This file is part of the PHPUnit_MockObject package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Framework_MockBuilderTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testMockBuilderRequiresClassName()
    {
        $mock = $this->getMockBuilder(\MolliePrefix\Mockable::class)->getMock();
        $this->assertTrue($mock instanceof \MolliePrefix\Mockable);
    }
    public function testByDefaultMocksAllMethods()
    {
        $mock = $this->getMockBuilder(\MolliePrefix\Mockable::class)->getMock();
        $this->assertNull($mock->mockableMethod());
        $this->assertNull($mock->anotherMockableMethod());
    }
    public function testMethodsToMockCanBeSpecified()
    {
        $mock = $this->getMockBuilder(\MolliePrefix\Mockable::class)->setMethods(['mockableMethod'])->getMock();
        $this->assertNull($mock->mockableMethod());
        $this->assertTrue($mock->anotherMockableMethod());
    }
    public function testMethodExceptionsToMockCanBeSpecified()
    {
        $mock = $this->getMockBuilder(\MolliePrefix\Mockable::class)->setMethodsExcept(['mockableMethod'])->getMock();
        $this->assertTrue($mock->mockableMethod());
        $this->assertNull($mock->anotherMockableMethod());
    }
    public function testEmptyMethodExceptionsToMockCanBeSpecified()
    {
        $mock = $this->getMockBuilder(\MolliePrefix\Mockable::class)->setMethodsExcept()->getMock();
        $this->assertNull($mock->mockableMethod());
        $this->assertNull($mock->anotherMockableMethod());
    }
    public function testByDefaultDoesNotPassArgumentsToTheConstructor()
    {
        $mock = $this->getMockBuilder(\MolliePrefix\Mockable::class)->getMock();
        $this->assertEquals([null, null], $mock->constructorArgs);
    }
    public function testMockClassNameCanBeSpecified()
    {
        $mock = $this->getMockBuilder(\MolliePrefix\Mockable::class)->setMockClassName('ACustomClassName')->getMock();
        $this->assertTrue($mock instanceof \MolliePrefix\ACustomClassName);
    }
    public function testConstructorArgumentsCanBeSpecified()
    {
        $mock = $this->getMockBuilder(\MolliePrefix\Mockable::class)->setConstructorArgs([23, 42])->getMock();
        $this->assertEquals([23, 42], $mock->constructorArgs);
    }
    public function testOriginalConstructorCanBeDisabled()
    {
        $mock = $this->getMockBuilder(\MolliePrefix\Mockable::class)->disableOriginalConstructor()->getMock();
        $this->assertNull($mock->constructorArgs);
    }
    public function testByDefaultOriginalCloneIsPreserved()
    {
        $mock = $this->getMockBuilder(\MolliePrefix\Mockable::class)->getMock();
        $cloned = clone $mock;
        $this->assertTrue($cloned->cloned);
    }
    public function testOriginalCloneCanBeDisabled()
    {
        $mock = $this->getMockBuilder(\MolliePrefix\Mockable::class)->disableOriginalClone()->getMock();
        $mock->cloned = \false;
        $cloned = clone $mock;
        $this->assertFalse($cloned->cloned);
    }
    public function testProvidesAFluentInterface()
    {
        $spec = $this->getMockBuilder(\MolliePrefix\Mockable::class)->setMethods(['mockableMethod'])->setConstructorArgs([])->setMockClassName('DummyClassName')->disableOriginalConstructor()->disableOriginalClone()->disableAutoload();
        $this->assertTrue($spec instanceof \MolliePrefix\PHPUnit_Framework_MockObject_MockBuilder);
    }
}
/*
 * This file is part of the PHPUnit_MockObject package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
\class_alias('MolliePrefix\\Framework_MockBuilderTest', 'Framework_MockBuilderTest', \false);
