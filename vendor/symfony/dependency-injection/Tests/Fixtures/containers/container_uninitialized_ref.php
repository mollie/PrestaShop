<?php

namespace _PhpScoper5ece82d7231e4;

use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference;
$container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->register('foo1', 'stdClass')->setPublic(\true);
$container->register('foo2', 'stdClass')->setPublic(\false);
$container->register('foo3', 'stdClass')->setPublic(\false);
$container->register('baz', 'stdClass')->setProperty('foo3', new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('foo3'))->setPublic(\true);
$container->register('bar', 'stdClass')->setProperty('foo1', new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('foo1', $container::IGNORE_ON_UNINITIALIZED_REFERENCE))->setProperty('foo2', new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('foo2', $container::IGNORE_ON_UNINITIALIZED_REFERENCE))->setProperty('foo3', new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('foo3', $container::IGNORE_ON_UNINITIALIZED_REFERENCE))->setProperty('closures', [new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('foo1', $container::IGNORE_ON_UNINITIALIZED_REFERENCE)), new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('foo2', $container::IGNORE_ON_UNINITIALIZED_REFERENCE)), new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('foo3', $container::IGNORE_ON_UNINITIALIZED_REFERENCE))])->setProperty('iter', new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Argument\IteratorArgument(['foo1' => new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('foo1', $container::IGNORE_ON_UNINITIALIZED_REFERENCE), 'foo2' => new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('foo2', $container::IGNORE_ON_UNINITIALIZED_REFERENCE), 'foo3' => new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('foo3', $container::IGNORE_ON_UNINITIALIZED_REFERENCE)]))->setPublic(\true);
return $container;
