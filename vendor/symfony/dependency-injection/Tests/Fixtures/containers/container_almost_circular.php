<?php

namespace _PhpScoper5eddef0da618a;

use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Definition;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\FooForCircularWithAddCalls;
$public = 'public' === $visibility;
$container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
// same visibility for deps
$container->register('foo', \_PhpScoper5eddef0da618a\FooCircular::class)->setPublic(\true)->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('bar'));
$container->register('bar', \_PhpScoper5eddef0da618a\BarCircular::class)->setPublic($public)->addMethodCall('addFoobar', [new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('foobar')]);
$container->register('foobar', \_PhpScoper5eddef0da618a\FoobarCircular::class)->setPublic($public)->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('foo'));
// mixed visibility for deps
$container->register('foo2', \_PhpScoper5eddef0da618a\FooCircular::class)->setPublic(\true)->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('bar2'));
$container->register('bar2', \_PhpScoper5eddef0da618a\BarCircular::class)->setPublic(!$public)->addMethodCall('addFoobar', [new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('foobar2')]);
$container->register('foobar2', \_PhpScoper5eddef0da618a\FoobarCircular::class)->setPublic($public)->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('foo2'));
// simple inline setter with internal reference
$container->register('bar3', \_PhpScoper5eddef0da618a\BarCircular::class)->setPublic(\true)->addMethodCall('addFoobar', [new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('foobar3'), new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('foobar3')]);
$container->register('foobar3', \_PhpScoper5eddef0da618a\FoobarCircular::class)->setPublic($public);
// loop with non-shared dep
$container->register('foo4', 'stdClass')->setPublic($public)->setShared(\false)->setProperty('foobar', new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('foobar4'));
$container->register('foobar4', 'stdClass')->setPublic(\true)->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('foo4'));
// loop on the constructor of a setter-injected dep with property
$container->register('foo5', 'stdClass')->setPublic(\true)->setProperty('bar', new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('bar5'));
$container->register('bar5', 'stdClass')->setPublic($public)->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('foo5'))->setProperty('foo', new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('foo5'));
// doctrine-like event system + some extra
$container->register('manager', 'stdClass')->setPublic(\true)->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('connection'));
$container->register('logger', 'stdClass')->setPublic(\true)->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('connection'))->setProperty('handler', (new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Definition('stdClass'))->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('manager')));
$container->register('connection', 'stdClass')->setPublic(\true)->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('dispatcher'))->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('config'));
$container->register('config', 'stdClass')->setPublic(\false)->setProperty('logger', new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('logger'));
$container->register('dispatcher', 'stdClass')->setPublic($public)->setLazy($public)->setProperty('subscriber', new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('subscriber'));
$container->register('subscriber', 'stdClass')->setPublic(\true)->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('manager'));
// doctrine-like event system + some extra (bis)
$container->register('manager2', 'stdClass')->setPublic(\true)->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('connection2'));
$container->register('logger2', 'stdClass')->setPublic(\false)->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('connection2'))->setProperty('handler2', (new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Definition('stdClass'))->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('manager2')));
$container->register('connection2', 'stdClass')->setPublic(\true)->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('dispatcher2'))->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('config2'));
$container->register('config2', 'stdClass')->setPublic(\false)->setProperty('logger2', new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('logger2'));
$container->register('dispatcher2', 'stdClass')->setPublic($public)->setLazy($public)->setProperty('subscriber2', new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('subscriber2'));
$container->register('subscriber2', 'stdClass')->setPublic(\false)->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('manager2'));
// doctrine-like event system with listener
$container->register('manager3', 'stdClass')->setLazy(\true)->setPublic(\true)->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('connection3'));
$container->register('connection3', 'stdClass')->setPublic($public)->setProperty('listener', [new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('listener3')]);
$container->register('listener3', 'stdClass')->setPublic(\true)->setProperty('manager', new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('manager3'));
// doctrine-like event system with small differences
$container->register('manager4', 'stdClass')->setLazy(\true)->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('connection4'));
$container->register('connection4', 'stdClass')->setPublic($public)->setProperty('listener', [new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('listener4')]);
$container->register('listener4', 'stdClass')->setPublic(\true)->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('manager4'));
// private service involved in a loop
$container->register('foo6', 'stdClass')->setPublic(\true)->setProperty('bar6', new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('bar6'));
$container->register('bar6', 'stdClass')->setPublic(\false)->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('foo6'));
$container->register('baz6', 'stdClass')->setPublic(\true)->setProperty('bar6', new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('bar6'));
// provided by Christian Schiffler
$container->register('root', 'stdClass')->setArguments([new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('level2'), new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('multiuse1')])->setPublic(\true);
$container->register('level2', \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\FooForCircularWithAddCalls::class)->addMethodCall('call', [new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('level3')]);
$container->register('multiuse1', 'stdClass');
$container->register('level3', 'stdClass')->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('level4'));
$container->register('level4', 'stdClass')->setArguments([new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('multiuse1'), new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('level5')]);
$container->register('level5', 'stdClass')->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('level6'));
$container->register('level6', \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\FooForCircularWithAddCalls::class)->addMethodCall('call', [new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('level5')]);
return $container;
