<?php

namespace _PhpScoper5ece82d7231e4\Symfony\Tests\InlineRequires;

use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Definition;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\includes\HotPath;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\ParentNotExists;
$container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->register(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\includes\HotPath\C1::class)->addTag('container.hot_path')->setPublic(\true);
$container->register(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\includes\HotPath\C2::class)->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\includes\HotPath\C3::class))->setPublic(\true);
$container->register(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\includes\HotPath\C3::class);
$container->register(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\ParentNotExists::class)->setPublic(\true);
return $container;
