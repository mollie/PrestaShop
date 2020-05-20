<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\LazyProxy\Instantiator;

use _PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Definition;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\LazyProxy\Instantiator\RealServiceInstantiator;
use stdClass;

/**
 * Tests for {@see \Symfony\Component\DependencyInjection\LazyProxy\Instantiator\RealServiceInstantiator}.
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class RealServiceInstantiatorTest extends TestCase
{
    public function testInstantiateProxy()
    {
        $instantiator = new RealServiceInstantiator();
        $instance = new stdClass();
        $container = $this->getMockBuilder('_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\ContainerInterface')->getMock();
        $callback = function () use($instance) {
            return $instance;
        };
        $this->assertSame($instance, $instantiator->instantiateProxy($container, new Definition(), 'foo', $callback));
    }
}
