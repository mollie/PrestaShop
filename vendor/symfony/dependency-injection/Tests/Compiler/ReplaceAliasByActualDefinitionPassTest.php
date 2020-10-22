<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ReplaceAliasByActualDefinitionPass;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Definition;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
require_once __DIR__ . '/../Fixtures/includes/foo.php';
class ReplaceAliasByActualDefinitionPassTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $aDefinition = $container->register('a', '\\stdClass');
        $aDefinition->setFactory([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('b'), 'createA']);
        $bDefinition = new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('\\stdClass');
        $bDefinition->setPublic(\false);
        $container->setDefinition('b', $bDefinition);
        $container->setAlias('a_alias', 'a');
        $container->setAlias('b_alias', 'b');
        $container->setAlias('container', 'service_container');
        $this->process($container);
        $this->assertTrue($container->has('a'), '->process() does nothing to public definitions.');
        $this->assertTrue($container->hasAlias('a_alias'));
        $this->assertFalse($container->has('b'), '->process() removes non-public definitions.');
        $this->assertTrue($container->has('b_alias') && !$container->hasAlias('b_alias'), '->process() replaces alias to actual.');
        $this->assertTrue($container->has('container'));
        $resolvedFactory = $aDefinition->getFactory();
        $this->assertSame('b_alias', (string) $resolvedFactory[0]);
    }
    public function testProcessWithInvalidAlias()
    {
        $this->expectException('InvalidArgumentException');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setAlias('a_alias', 'a');
        $this->process($container);
    }
    protected function process(\MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ReplaceAliasByActualDefinitionPass();
        $pass->process($container);
    }
}
