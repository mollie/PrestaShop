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
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutoAliasServicePass;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
class AutoAliasServicePassTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testProcessWithMissingParameter()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\ParameterNotFoundException');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('example')->addTag('auto_alias', ['format' => '%non_existing%.example']);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutoAliasServicePass();
        $pass->process($container);
    }
    public function testProcessWithMissingFormat()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('example')->addTag('auto_alias', []);
        $container->setParameter('existing', 'mysql');
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutoAliasServicePass();
        $pass->process($container);
    }
    public function testProcessWithNonExistingAlias()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('example', 'MolliePrefix\\Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\ServiceClassDefault')->addTag('auto_alias', ['format' => '%existing%.example']);
        $container->setParameter('existing', 'mysql');
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutoAliasServicePass();
        $pass->process($container);
        $this->assertEquals('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\ServiceClassDefault', $container->getDefinition('example')->getClass());
    }
    public function testProcessWithExistingAlias()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('example', 'MolliePrefix\\Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\ServiceClassDefault')->addTag('auto_alias', ['format' => '%existing%.example']);
        $container->register('mysql.example', 'MolliePrefix\\Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\ServiceClassMysql');
        $container->setParameter('existing', 'mysql');
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutoAliasServicePass();
        $pass->process($container);
        $this->assertTrue($container->hasAlias('example'));
        $this->assertEquals('mysql.example', $container->getAlias('example'));
        $this->assertSame('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\ServiceClassMysql', $container->getDefinition('mysql.example')->getClass());
    }
    public function testProcessWithManualAlias()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('example', 'MolliePrefix\\Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\ServiceClassDefault')->addTag('auto_alias', ['format' => '%existing%.example']);
        $container->register('mysql.example', 'MolliePrefix\\Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\ServiceClassMysql');
        $container->register('mariadb.example', 'MolliePrefix\\Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\ServiceClassMariaDb');
        $container->setAlias('example', 'mariadb.example');
        $container->setParameter('existing', 'mysql');
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutoAliasServicePass();
        $pass->process($container);
        $this->assertTrue($container->hasAlias('example'));
        $this->assertEquals('mariadb.example', $container->getAlias('example'));
        $this->assertSame('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\ServiceClassMariaDb', $container->getDefinition('mariadb.example')->getClass());
    }
}
class ServiceClassDefault
{
}
class ServiceClassMysql extends \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\ServiceClassDefault
{
}
class ServiceClassMariaDb extends \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\ServiceClassMysql
{
}
