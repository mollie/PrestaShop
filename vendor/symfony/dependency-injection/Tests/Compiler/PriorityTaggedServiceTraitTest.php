<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Compiler;

use _PhpScoper5eddef0da618a\PHPUnit\Framework\TestCase;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference;
class PriorityTaggedServiceTraitTest extends \_PhpScoper5eddef0da618a\PHPUnit\Framework\TestCase
{
    public function testThatCacheWarmersAreProcessedInPriorityOrder()
    {
        $services = ['my_service1' => ['my_custom_tag' => ['priority' => 100]], 'my_service2' => ['my_custom_tag' => ['priority' => 200]], 'my_service3' => ['my_custom_tag' => ['priority' => -501]], 'my_service4' => ['my_custom_tag' => []], 'my_service5' => ['my_custom_tag' => ['priority' => -1]], 'my_service6' => ['my_custom_tag' => ['priority' => -500]], 'my_service7' => ['my_custom_tag' => ['priority' => -499]], 'my_service8' => ['my_custom_tag' => ['priority' => 1]], 'my_service9' => ['my_custom_tag' => ['priority' => -2]], 'my_service10' => ['my_custom_tag' => ['priority' => -1000]], 'my_service11' => ['my_custom_tag' => ['priority' => -1001]], 'my_service12' => ['my_custom_tag' => ['priority' => -1002]], 'my_service13' => ['my_custom_tag' => ['priority' => -1003]], 'my_service14' => ['my_custom_tag' => ['priority' => -1000]], 'my_service15' => ['my_custom_tag' => ['priority' => 1]], 'my_service16' => ['my_custom_tag' => ['priority' => -1]], 'my_service17' => ['my_custom_tag' => ['priority' => 200]], 'my_service18' => ['my_custom_tag' => ['priority' => 100]], 'my_service19' => ['my_custom_tag' => []]];
        $container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
        foreach ($services as $id => $tags) {
            $definition = $container->register($id);
            foreach ($tags as $name => $attributes) {
                $definition->addTag($name, $attributes);
            }
        }
        $expected = [new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('my_service2'), new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('my_service17'), new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('my_service1'), new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('my_service18'), new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('my_service8'), new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('my_service15'), new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('my_service4'), new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('my_service19'), new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('my_service5'), new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('my_service16'), new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('my_service9'), new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('my_service7'), new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('my_service6'), new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('my_service3'), new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('my_service10'), new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('my_service14'), new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('my_service11'), new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('my_service12'), new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('my_service13')];
        $priorityTaggedServiceTraitImplementation = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Compiler\PriorityTaggedServiceTraitImplementation();
        $this->assertEquals($expected, $priorityTaggedServiceTraitImplementation->test('my_custom_tag', $container));
    }
}
class PriorityTaggedServiceTraitImplementation
{
    use PriorityTaggedServiceTrait;
    public function test($tagName, \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        return $this->findAndSortTaggedServices($tagName, $container);
    }
}
