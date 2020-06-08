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
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Compiler\CheckReferenceValidityPass;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference;
class CheckReferenceValidityPassTest extends \_PhpScoper5eddef0da618a\PHPUnit\Framework\TestCase
{
    public function testProcessDetectsReferenceToAbstractDefinition()
    {
        $this->expectException('RuntimeException');
        $container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a')->setAbstract(\true);
        $container->register('b')->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('a'));
        $this->process($container);
    }
    public function testProcess()
    {
        $container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a')->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('b'));
        $container->register('b');
        $this->process($container);
        $this->addToAssertionCount(1);
    }
    protected function process(\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $pass = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Compiler\CheckReferenceValidityPass();
        $pass->process($container);
    }
}
