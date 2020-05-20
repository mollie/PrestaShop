<?php

namespace _PhpScoper5ea00cc67502b;

use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Definition;
require_once __DIR__ . '/../includes/classes.php';
$container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->setParameter('env(FOO)', '_PhpScoper5ea00cc67502b\\Bar\\FaooClass');
$container->setParameter('foo', '%env(FOO)%');
$container->register('service_from_anonymous_factory', '%foo%')->setFactory([new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Definition('%foo%'), 'getInstance'])->setPublic(\true);
$anonymousServiceWithFactory = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Definition('_PhpScoper5ea00cc67502b\\Bar\\FooClass');
$anonymousServiceWithFactory->setFactory('Bar\\FooClass::getInstance');
$container->register('service_with_method_call_and_factory', '_PhpScoper5ea00cc67502b\\Bar\\FooClass')->addMethodCall('setBar', [$anonymousServiceWithFactory])->setPublic(\true);
return $container;
