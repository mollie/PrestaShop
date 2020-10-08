<?php

namespace MolliePrefix;

use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Definition;
require_once __DIR__ . '/../includes/classes.php';
$container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->setParameter('env(FOO)', 'MolliePrefix\\Bar\\FaooClass');
$container->setParameter('foo', '%env(FOO)%');
$container->register('service_from_anonymous_factory', '%foo%')->setFactory([new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('%foo%'), 'getInstance'])->setPublic(\true);
$anonymousServiceWithFactory = new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('MolliePrefix\\Bar\\FooClass');
$anonymousServiceWithFactory->setFactory('Bar\\FooClass::getInstance');
$container->register('service_with_method_call_and_factory', 'MolliePrefix\\Bar\\FooClass')->addMethodCall('setBar', [$anonymousServiceWithFactory])->setPublic(\true);
return $container;
