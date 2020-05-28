<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Compiler;

use _PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\AutoAliasServicePass;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder;
class AutoAliasServicePassTest extends \_PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase
{
    public function testProcessWithMissingParameter()
    {
        $this->expectException('_PhpScoper5ece82d7231e4\\Symfony\\Component\\DependencyInjection\\Exception\\ParameterNotFoundException');
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('example')->addTag('auto_alias', ['format' => '%non_existing%.example']);
        $pass = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\AutoAliasServicePass();
        $pass->process($container);
    }
    public function testProcessWithMissingFormat()
    {
        $this->expectException('_PhpScoper5ece82d7231e4\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('example')->addTag('auto_alias', []);
        $container->setParameter('existing', 'mysql');
        $pass = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\AutoAliasServicePass();
        $pass->process($container);
    }
    public function testProcessWithNonExistingAlias()
    {
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('example', '_PhpScoper5ece82d7231e4\\Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\ServiceClassDefault')->addTag('auto_alias', ['format' => '%existing%.example']);
        $container->setParameter('existing', 'mysql');
        $pass = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\AutoAliasServicePass();
        $pass->process($container);
        $this->assertEquals('_PhpScoper5ece82d7231e4\\Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\ServiceClassDefault', $container->getDefinition('example')->getClass());
    }
    public function testProcessWithExistingAlias()
    {
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('example', '_PhpScoper5ece82d7231e4\\Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\ServiceClassDefault')->addTag('auto_alias', ['format' => '%existing%.example']);
        $container->register('mysql.example', '_PhpScoper5ece82d7231e4\\Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\ServiceClassMysql');
        $container->setParameter('existing', 'mysql');
        $pass = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\AutoAliasServicePass();
        $pass->process($container);
        $this->assertTrue($container->hasAlias('example'));
        $this->assertEquals('mysql.example', $container->getAlias('example'));
        $this->assertSame('_PhpScoper5ece82d7231e4\\Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\ServiceClassMysql', $container->getDefinition('mysql.example')->getClass());
    }
    public function testProcessWithManualAlias()
    {
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('example', '_PhpScoper5ece82d7231e4\\Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\ServiceClassDefault')->addTag('auto_alias', ['format' => '%existing%.example']);
        $container->register('mysql.example', '_PhpScoper5ece82d7231e4\\Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\ServiceClassMysql');
        $container->register('mariadb.example', '_PhpScoper5ece82d7231e4\\Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\ServiceClassMariaDb');
        $container->setAlias('example', 'mariadb.example');
        $container->setParameter('existing', 'mysql');
        $pass = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\AutoAliasServicePass();
        $pass->process($container);
        $this->assertTrue($container->hasAlias('example'));
        $this->assertEquals('mariadb.example', $container->getAlias('example'));
        $this->assertSame('_PhpScoper5ece82d7231e4\\Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\ServiceClassMariaDb', $container->getDefinition('mariadb.example')->getClass());
    }
}
class ServiceClassDefault
{
}
class ServiceClassMysql extends \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Compiler\ServiceClassDefault
{
}
class ServiceClassMariaDb extends \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Compiler\ServiceClassMysql
{
}
