<?php

namespace MolliePrefix;

use MolliePrefix\Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use MolliePrefix\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
$container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->register('foo1', 'stdClass')->setPublic(\true);
$container->register('foo2', 'stdClass')->setPublic(\false);
$container->register('foo3', 'stdClass')->setPublic(\false);
$container->register('baz', 'stdClass')->setProperty('foo3', new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo3'))->setPublic(\true);
$container->register('bar', 'stdClass')->setProperty('foo1', new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo1', $container::IGNORE_ON_UNINITIALIZED_REFERENCE))->setProperty('foo2', new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo2', $container::IGNORE_ON_UNINITIALIZED_REFERENCE))->setProperty('foo3', new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo3', $container::IGNORE_ON_UNINITIALIZED_REFERENCE))->setProperty('closures', [new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo1', $container::IGNORE_ON_UNINITIALIZED_REFERENCE)), new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo2', $container::IGNORE_ON_UNINITIALIZED_REFERENCE)), new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo3', $container::IGNORE_ON_UNINITIALIZED_REFERENCE))])->setProperty('iter', new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\IteratorArgument(['foo1' => new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo1', $container::IGNORE_ON_UNINITIALIZED_REFERENCE), 'foo2' => new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo2', $container::IGNORE_ON_UNINITIALIZED_REFERENCE), 'foo3' => new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo3', $container::IGNORE_ON_UNINITIALIZED_REFERENCE)]))->setPublic(\true);
return $container;
