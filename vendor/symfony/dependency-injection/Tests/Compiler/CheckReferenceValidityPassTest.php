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
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\CheckReferenceValidityPass;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
class CheckReferenceValidityPassTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testProcessDetectsReferenceToAbstractDefinition()
    {
        $this->expectException('RuntimeException');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a')->setAbstract(\true);
        $container->register('b')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('a'));
        $this->process($container);
    }
    public function testProcess()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('b'));
        $container->register('b');
        $this->process($container);
        $this->addToAssertionCount(1);
    }
    protected function process(\MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\CheckReferenceValidityPass();
        $pass->process($container);
    }
}
