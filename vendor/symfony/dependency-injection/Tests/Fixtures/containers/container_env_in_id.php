<?php

namespace _PhpScoper5eddef0da618a;

use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Definition;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference;
$container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->setParameter('env(BAR)', 'bar');
$container->register('foo', 'stdClass')->setPublic(\true)->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('bar_%env(BAR)%'))->addArgument(['baz_%env(BAR)%' => new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('baz_%env(BAR)%')]);
$container->register('bar', 'stdClass')->setPublic(\true)->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('bar_%env(BAR)%'));
$container->register('bar_%env(BAR)%', 'stdClass')->setPublic(\false);
$container->register('baz_%env(BAR)%', 'stdClass')->setPublic(\false);
return $container;
