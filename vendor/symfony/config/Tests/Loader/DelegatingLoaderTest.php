<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Config\Tests\Loader;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Config\Loader\DelegatingLoader;
use MolliePrefix\Symfony\Component\Config\Loader\LoaderResolver;
class DelegatingLoaderTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        new \MolliePrefix\Symfony\Component\Config\Loader\DelegatingLoader($resolver = new \MolliePrefix\Symfony\Component\Config\Loader\LoaderResolver());
        $this->assertTrue(\true, '__construct() takes a loader resolver as its first argument');
    }
    public function testGetSetResolver()
    {
        $resolver = new \MolliePrefix\Symfony\Component\Config\Loader\LoaderResolver();
        $loader = new \MolliePrefix\Symfony\Component\Config\Loader\DelegatingLoader($resolver);
        $this->assertSame($resolver, $loader->getResolver(), '->getResolver() gets the resolver loader');
        $loader->setResolver($resolver = new \MolliePrefix\Symfony\Component\Config\Loader\LoaderResolver());
        $this->assertSame($resolver, $loader->getResolver(), '->setResolver() sets the resolver loader');
    }
    public function testSupports()
    {
        $loader1 = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\Config\\Loader\\LoaderInterface')->getMock();
        $loader1->expects($this->once())->method('supports')->willReturn(\true);
        $loader = new \MolliePrefix\Symfony\Component\Config\Loader\DelegatingLoader(new \MolliePrefix\Symfony\Component\Config\Loader\LoaderResolver([$loader1]));
        $this->assertTrue($loader->supports('foo.xml'), '->supports() returns true if the resource is loadable');
        $loader1 = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\Config\\Loader\\LoaderInterface')->getMock();
        $loader1->expects($this->once())->method('supports')->willReturn(\false);
        $loader = new \MolliePrefix\Symfony\Component\Config\Loader\DelegatingLoader(new \MolliePrefix\Symfony\Component\Config\Loader\LoaderResolver([$loader1]));
        $this->assertFalse($loader->supports('foo.foo'), '->supports() returns false if the resource is not loadable');
    }
    public function testLoad()
    {
        $loader = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\Config\\Loader\\LoaderInterface')->getMock();
        $loader->expects($this->once())->method('supports')->willReturn(\true);
        $loader->expects($this->once())->method('load');
        $resolver = new \MolliePrefix\Symfony\Component\Config\Loader\LoaderResolver([$loader]);
        $loader = new \MolliePrefix\Symfony\Component\Config\Loader\DelegatingLoader($resolver);
        $loader->load('foo');
    }
    public function testLoadThrowsAnExceptionIfTheResourceCannotBeLoaded()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\Config\\Exception\\FileLoaderLoadException');
        $loader = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\Config\\Loader\\LoaderInterface')->getMock();
        $loader->expects($this->once())->method('supports')->willReturn(\false);
        $resolver = new \MolliePrefix\Symfony\Component\Config\Loader\LoaderResolver([$loader]);
        $loader = new \MolliePrefix\Symfony\Component\Config\Loader\DelegatingLoader($resolver);
        $loader->load('foo');
    }
}
