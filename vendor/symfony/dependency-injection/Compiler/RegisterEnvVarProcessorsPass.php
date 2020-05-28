<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler;

use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\EnvVarProcessor;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\EnvVarProcessorInterface;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ParameterBag\EnvPlaceholderParameterBag;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ServiceLocator;
/**
 * Creates the container.env_var_processors_locator service.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class RegisterEnvVarProcessorsPass implements \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    private static $allowedTypes = ['array', 'bool', 'float', 'int', 'string'];
    public function process(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $bag = $container->getParameterBag();
        $types = [];
        $processors = [];
        foreach ($container->findTaggedServiceIds('container.env_var_processor') as $id => $tags) {
            if (!($r = $container->getReflectionClass($class = $container->getDefinition($id)->getClass()))) {
                throw new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Class "%s" used for service "%s" cannot be found.', $class, $id));
            } elseif (!$r->isSubclassOf(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\EnvVarProcessorInterface::class)) {
                throw new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Service "%s" must implement interface "%s".', $id, \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\EnvVarProcessorInterface::class));
            }
            foreach ($class::getProvidedTypes() as $prefix => $type) {
                $processors[$prefix] = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference($id));
                $types[$prefix] = self::validateProvidedTypes($type, $class);
            }
        }
        if ($bag instanceof \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ParameterBag\EnvPlaceholderParameterBag) {
            foreach (\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\EnvVarProcessor::getProvidedTypes() as $prefix => $type) {
                if (!isset($types[$prefix])) {
                    $types[$prefix] = self::validateProvidedTypes($type, \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\EnvVarProcessor::class);
                }
            }
            $bag->setProvidedTypes($types);
        }
        if ($processors) {
            $container->register('container.env_var_processors_locator', \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ServiceLocator::class)->setPublic(\true)->setArguments([$processors]);
        }
    }
    private static function validateProvidedTypes($types, $class)
    {
        $types = \explode('|', $types);
        foreach ($types as $type) {
            if (!\in_array($type, self::$allowedTypes)) {
                throw new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Invalid type "%s" returned by "%s::getProvidedTypes()", expected one of "%s".', $type, $class, \implode('", "', self::$allowedTypes)));
            }
        }
        return $types;
    }
}
