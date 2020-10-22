<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveTaggedIteratorArgumentPass;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class ResolveTaggedIteratorArgumentPassTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a', 'stdClass')->addTag('foo');
        $container->register('b', 'stdClass')->addTag('foo', ['priority' => 20]);
        $container->register('c', 'stdClass')->addTag('foo', ['priority' => 10]);
        $container->register('d', 'stdClass')->setProperty('foos', new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument('foo'));
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveTaggedIteratorArgumentPass())->process($container);
        $properties = $container->getDefinition('d')->getProperties();
        $expected = new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument('foo');
        $expected->setValues([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('b'), new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('c'), new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('a')]);
        $this->assertEquals($expected, $properties['foos']);
    }
}
