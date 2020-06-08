<?php

namespace _PhpScoper5eddef0da618a\Symfony\Tests\InlineRequires;

use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Definition;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\includes\HotPath;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\ParentNotExists;
$container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->register(\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\includes\HotPath\C1::class)->addTag('container.hot_path')->setPublic(\true);
$container->register(\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\includes\HotPath\C2::class)->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference(\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\includes\HotPath\C3::class))->setPublic(\true);
$container->register(\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\includes\HotPath\C3::class);
$container->register(\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\ParentNotExists::class)->setPublic(\true);
return $container;
