<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler;

use _PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\RegisterEnvVarProcessorsPass;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\EnvVarProcessorInterface;
class RegisterEnvVarProcessorsPassTest extends \_PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase
{
    public function testSimpleProcessor()
    {
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\SimpleProcessor::class)->addTag('container.env_var_processor');
        (new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\RegisterEnvVarProcessorsPass())->process($container);
        $this->assertTrue($container->has('container.env_var_processors_locator'));
        $this->assertInstanceOf(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\SimpleProcessor::class, $container->get('container.env_var_processors_locator')->get('foo'));
        $expected = ['foo' => ['string'], 'base64' => ['string'], 'bool' => ['bool'], 'const' => ['bool', 'int', 'float', 'string', 'array'], 'file' => ['string'], 'float' => ['float'], 'int' => ['int'], 'json' => ['array'], 'resolve' => ['string'], 'string' => ['string']];
        $this->assertSame($expected, $container->getParameterBag()->getProvidedTypes());
    }
    public function testNoProcessor()
    {
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        (new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\RegisterEnvVarProcessorsPass())->process($container);
        $this->assertFalse($container->has('container.env_var_processors_locator'));
    }
    public function testBadProcessor()
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid type "foo" returned by "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\BadProcessor::getProvidedTypes()", expected one of "array", "bool", "float", "int", "string".');
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\BadProcessor::class)->addTag('container.env_var_processor');
        (new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\RegisterEnvVarProcessorsPass())->process($container);
    }
}
class SimpleProcessor implements \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\EnvVarProcessorInterface
{
    public function getEnv($prefix, $name, \Closure $getEnv)
    {
        return $getEnv($name);
    }
    public static function getProvidedTypes()
    {
        return ['foo' => 'string'];
    }
}
class BadProcessor extends \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\SimpleProcessor
{
    public static function getProvidedTypes()
    {
        return ['foo' => 'string|foo'];
    }
}
