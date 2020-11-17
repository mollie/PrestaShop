<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\DependencyInjection\Tests\LazyProxy\Instantiator;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\DependencyInjection\Definition;
use MolliePrefix\Symfony\Component\DependencyInjection\LazyProxy\Instantiator\RealServiceInstantiator;
/**
 * Tests for {@see \Symfony\Component\DependencyInjection\LazyProxy\Instantiator\RealServiceInstantiator}.
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class RealServiceInstantiatorTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testInstantiateProxy()
    {
        $instantiator = new \MolliePrefix\Symfony\Component\DependencyInjection\LazyProxy\Instantiator\RealServiceInstantiator();
        $instance = new \stdClass();
        $container = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\DependencyInjection\\ContainerInterface')->getMock();
        $callback = function () use($instance) {
            return $instance;
        };
        $this->assertSame($instance, $instantiator->instantiateProxy($container, new \MolliePrefix\Symfony\Component\DependencyInjection\Definition(), 'foo', $callback));
    }
}
