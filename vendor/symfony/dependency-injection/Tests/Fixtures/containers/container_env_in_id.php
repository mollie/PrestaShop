<?php

namespace MolliePrefix;

use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Definition;
use MolliePrefix\Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
$container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->setParameter('env(BAR)', 'bar');
$container->register('foo', 'stdClass')->setPublic(\true)->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar_%env(BAR)%'))->addArgument(['baz_%env(BAR)%' => new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('baz_%env(BAR)%')]);
$container->register('bar', 'stdClass')->setPublic(\true)->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar_%env(BAR)%'));
$container->register('bar_%env(BAR)%', 'stdClass')->setPublic(\false);
$container->register('baz_%env(BAR)%', 'stdClass')->setPublic(\false);
return $container;
