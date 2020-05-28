<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ece82d7231e4\Symfony\Component\Config\Tests\Resource;

use _PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase;
use _PhpScoper5ece82d7231e4\Symfony\Component\Config\Resource\ClassExistenceResource;
use _PhpScoper5ece82d7231e4\Symfony\Component\Config\Tests\Fixtures\BadFileName;
use _PhpScoper5ece82d7231e4\Symfony\Component\Config\Tests\Fixtures\BadParent;
use _PhpScoper5ece82d7231e4\Symfony\Component\Config\Tests\Fixtures\ParseError;
use _PhpScoper5ece82d7231e4\Symfony\Component\Config\Tests\Fixtures\Resource\ConditionalClass;
class ClassExistenceResourceTest extends \_PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase
{
    public function testToString()
    {
        $res = new \_PhpScoper5ece82d7231e4\Symfony\Component\Config\Resource\ClassExistenceResource('BarClass');
        $this->assertSame('BarClass', (string) $res);
    }
    public function testGetResource()
    {
        $res = new \_PhpScoper5ece82d7231e4\Symfony\Component\Config\Resource\ClassExistenceResource('BarClass');
        $this->assertSame('BarClass', $res->getResource());
    }
    public function testIsFreshWhenClassDoesNotExist()
    {
        $res = new \_PhpScoper5ece82d7231e4\Symfony\Component\Config\Resource\ClassExistenceResource('_PhpScoper5ece82d7231e4\\Symfony\\Component\\Config\\Tests\\Fixtures\\BarClass');
        $this->assertTrue($res->isFresh(\time()));
        eval(<<<EOF
namespace Symfony\\Component\\Config\\Tests\\Fixtures;

class BarClass
{
}
EOF
);
        $this->assertFalse($res->isFresh(\time()));
    }
    public function testIsFreshWhenClassExists()
    {
        $res = new \_PhpScoper5ece82d7231e4\Symfony\Component\Config\Resource\ClassExistenceResource('_PhpScoper5ece82d7231e4\\Symfony\\Component\\Config\\Tests\\Resource\\ClassExistenceResourceTest');
        $this->assertTrue($res->isFresh(\time()));
    }
    public function testExistsKo()
    {
        \spl_autoload_register($autoloader = function ($class) use(&$loadedClass) {
            $loadedClass = $class;
        });
        try {
            $res = new \_PhpScoper5ece82d7231e4\Symfony\Component\Config\Resource\ClassExistenceResource('MissingFooClass');
            $this->assertTrue($res->isFresh(0));
            $this->assertSame('MissingFooClass', $loadedClass);
            $loadedClass = 123;
            new \_PhpScoper5ece82d7231e4\Symfony\Component\Config\Resource\ClassExistenceResource('MissingFooClass', \false);
            $this->assertSame(123, $loadedClass);
        } finally {
            \spl_autoload_unregister($autoloader);
        }
    }
    public function testBadParentWithTimestamp()
    {
        $res = new \_PhpScoper5ece82d7231e4\Symfony\Component\Config\Resource\ClassExistenceResource(\_PhpScoper5ece82d7231e4\Symfony\Component\Config\Tests\Fixtures\BadParent::class, \false);
        $this->assertTrue($res->isFresh(\time()));
    }
    public function testBadParentWithNoTimestamp()
    {
        $this->expectException('ReflectionException');
        $this->expectExceptionMessage('Class "Symfony\\Component\\Config\\Tests\\Fixtures\\MissingParent" not found while loading "Symfony\\Component\\Config\\Tests\\Fixtures\\BadParent".');
        $res = new \_PhpScoper5ece82d7231e4\Symfony\Component\Config\Resource\ClassExistenceResource(\_PhpScoper5ece82d7231e4\Symfony\Component\Config\Tests\Fixtures\BadParent::class, \false);
        $res->isFresh(0);
    }
    public function testBadFileName()
    {
        $this->expectException('ReflectionException');
        $this->expectExceptionMessage('Mismatch between file name and class name.');
        $res = new \_PhpScoper5ece82d7231e4\Symfony\Component\Config\Resource\ClassExistenceResource(\_PhpScoper5ece82d7231e4\Symfony\Component\Config\Tests\Fixtures\BadFileName::class, \false);
        $res->isFresh(0);
    }
    public function testBadFileNameBis()
    {
        $this->expectException('ReflectionException');
        $this->expectExceptionMessage('Mismatch between file name and class name.');
        $res = new \_PhpScoper5ece82d7231e4\Symfony\Component\Config\Resource\ClassExistenceResource(\_PhpScoper5ece82d7231e4\Symfony\Component\Config\Tests\Fixtures\BadFileName::class, \false);
        $res->isFresh(0);
    }
    public function testConditionalClass()
    {
        $res = new \_PhpScoper5ece82d7231e4\Symfony\Component\Config\Resource\ClassExistenceResource(\_PhpScoper5ece82d7231e4\Symfony\Component\Config\Tests\Fixtures\Resource\ConditionalClass::class, \false);
        $this->assertFalse($res->isFresh(0));
    }
    /**
     * @requires PHP 7
     */
    public function testParseError()
    {
        $this->expectException('ParseError');
        $res = new \_PhpScoper5ece82d7231e4\Symfony\Component\Config\Resource\ClassExistenceResource(\_PhpScoper5ece82d7231e4\Symfony\Component\Config\Tests\Fixtures\ParseError::class, \false);
        $res->isFresh(0);
    }
}
