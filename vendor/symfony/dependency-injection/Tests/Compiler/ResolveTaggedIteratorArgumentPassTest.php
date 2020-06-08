<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Compiler;

use _PhpScoper5eddef0da618a\PHPUnit\Framework\TestCase;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Compiler\ResolveTaggedIteratorArgumentPass;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference;
/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class ResolveTaggedIteratorArgumentPassTest extends \_PhpScoper5eddef0da618a\PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a', 'stdClass')->addTag('foo');
        $container->register('b', 'stdClass')->addTag('foo', ['priority' => 20]);
        $container->register('c', 'stdClass')->addTag('foo', ['priority' => 10]);
        $container->register('d', 'stdClass')->setProperty('foos', new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument('foo'));
        (new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Compiler\ResolveTaggedIteratorArgumentPass())->process($container);
        $properties = $container->getDefinition('d')->getProperties();
        $expected = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument('foo');
        $expected->setValues([new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('b'), new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('c'), new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('a')]);
        $this->assertEquals($expected, $properties['foos']);
    }
}
