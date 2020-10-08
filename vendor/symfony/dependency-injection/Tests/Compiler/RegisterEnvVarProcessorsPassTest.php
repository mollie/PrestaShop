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
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\RegisterEnvVarProcessorsPass;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\EnvVarProcessorInterface;
class RegisterEnvVarProcessorsPassTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testSimpleProcessor()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\SimpleProcessor::class)->addTag('container.env_var_processor');
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\RegisterEnvVarProcessorsPass())->process($container);
        $this->assertTrue($container->has('container.env_var_processors_locator'));
        $this->assertInstanceOf(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\SimpleProcessor::class, $container->get('container.env_var_processors_locator')->get('foo'));
        $expected = ['foo' => ['string'], 'base64' => ['string'], 'bool' => ['bool'], 'const' => ['bool', 'int', 'float', 'string', 'array'], 'file' => ['string'], 'float' => ['float'], 'int' => ['int'], 'json' => ['array'], 'resolve' => ['string'], 'string' => ['string']];
        $this->assertSame($expected, $container->getParameterBag()->getProvidedTypes());
    }
    public function testNoProcessor()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\RegisterEnvVarProcessorsPass())->process($container);
        $this->assertFalse($container->has('container.env_var_processors_locator'));
    }
    public function testBadProcessor()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid type "foo" returned by "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\BadProcessor::getProvidedTypes()", expected one of "array", "bool", "float", "int", "string".');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\BadProcessor::class)->addTag('container.env_var_processor');
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\RegisterEnvVarProcessorsPass())->process($container);
    }
}
class SimpleProcessor implements \MolliePrefix\Symfony\Component\DependencyInjection\EnvVarProcessorInterface
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
class BadProcessor extends \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\SimpleProcessor
{
    public static function getProvidedTypes()
    {
        return ['foo' => 'string|foo'];
    }
}
