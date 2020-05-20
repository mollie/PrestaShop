<?php

namespace _PhpScoper5ea00cc67502b\Symfony\Tests\InlineRequires;

use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Definition;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\includes\HotPath;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\includes\HotPath\C1;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\includes\HotPath\C2;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\includes\HotPath\C3;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\ParentNotExists;
$container = new ContainerBuilder();
$container->register(C1::class)->addTag('container.hot_path')->setPublic(true);
$container->register(C2::class)->addArgument(new Reference(C3::class))->setPublic(true);
$container->register(C3::class);
$container->register(ParentNotExists::class)->setPublic(true);
return $container;
