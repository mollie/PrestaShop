<?php

namespace MolliePrefix\Symfony\Tests\InlineRequires;

use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Definition;
use MolliePrefix\Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\includes\HotPath;
use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\ParentNotExists;
$container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\includes\HotPath\C1::class)->addTag('container.hot_path')->setPublic(\true);
$container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\includes\HotPath\C2::class)->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\includes\HotPath\C3::class))->setPublic(\true);
$container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\includes\HotPath\C3::class);
$container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\ParentNotExists::class)->setPublic(\true);
return $container;
