<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\DependencyInjection\Compiler;

use MolliePrefix\Symfony\Component\DependencyInjection\Argument\BoundArgument;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Definition;
use MolliePrefix\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use MolliePrefix\Symfony\Component\DependencyInjection\Exception\RuntimeException;
use MolliePrefix\Symfony\Component\DependencyInjection\LazyProxy\ProxyHelper;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
use MolliePrefix\Symfony\Component\DependencyInjection\TypedReference;
/**
 * @author Guilhem Niot <guilhem.niot@gmail.com>
 */
class ResolveBindingsPass extends \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AbstractRecursivePass
{
    private $usedBindings = [];
    private $unusedBindings = [];
    private $errorMessages = [];
    /**
     * {@inheritdoc}
     */
    public function process(\MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $this->usedBindings = $container->getRemovedBindingIds();
        try {
            parent::process($container);
            foreach ($this->unusedBindings as list($key, $serviceId)) {
                $message = \sprintf('Unused binding "%s" in service "%s".', $key, $serviceId);
                if ($this->errorMessages) {
                    $message .= \sprintf("\nCould be related to%s:", 1 < \count($this->errorMessages) ? ' one of' : '');
                }
                foreach ($this->errorMessages as $m) {
                    $message .= "\n - " . $m;
                }
                throw new \MolliePrefix\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException($message);
            }
        } finally {
            $this->usedBindings = [];
            $this->unusedBindings = [];
            $this->errorMessages = [];
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function processValue($value, $isRoot = \false)
    {
        if ($value instanceof \MolliePrefix\Symfony\Component\DependencyInjection\TypedReference && $value->getType() === $this->container->normalizeId($value)) {
            // Already checked
            $bindings = $this->container->getDefinition($this->currentId)->getBindings();
            if (isset($bindings[$value->getType()])) {
                return $this->getBindingValue($bindings[$value->getType()]);
            }
            return parent::processValue($value, $isRoot);
        }
        if (!$value instanceof \MolliePrefix\Symfony\Component\DependencyInjection\Definition || !($bindings = $value->getBindings())) {
            return parent::processValue($value, $isRoot);
        }
        foreach ($bindings as $key => $binding) {
            list($bindingValue, $bindingId, $used) = $binding->getValues();
            if ($used) {
                $this->usedBindings[$bindingId] = \true;
                unset($this->unusedBindings[$bindingId]);
            } elseif (!isset($this->usedBindings[$bindingId])) {
                $this->unusedBindings[$bindingId] = [$key, $this->currentId];
            }
            if (isset($key[0]) && '$' === $key[0]) {
                continue;
            }
            if (null !== $bindingValue && !$bindingValue instanceof \MolliePrefix\Symfony\Component\DependencyInjection\Reference && !$bindingValue instanceof \MolliePrefix\Symfony\Component\DependencyInjection\Definition) {
                throw new \MolliePrefix\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Invalid value for binding key "%s" for service "%s": expected null, an instance of "%s" or an instance of "%s", "%s" given.', $key, $this->currentId, \MolliePrefix\Symfony\Component\DependencyInjection\Reference::class, \MolliePrefix\Symfony\Component\DependencyInjection\Definition::class, \gettype($bindingValue)));
            }
        }
        if ($value->isAbstract()) {
            return parent::processValue($value, $isRoot);
        }
        $calls = $value->getMethodCalls();
        try {
            if ($constructor = $this->getConstructor($value, \false)) {
                $calls[] = [$constructor, $value->getArguments()];
            }
        } catch (\MolliePrefix\Symfony\Component\DependencyInjection\Exception\RuntimeException $e) {
            $this->errorMessages[] = $e->getMessage();
            $this->container->getDefinition($this->currentId)->addError($e->getMessage());
            return parent::processValue($value, $isRoot);
        }
        foreach ($calls as $i => $call) {
            list($method, $arguments) = $call;
            if ($method instanceof \ReflectionFunctionAbstract) {
                $reflectionMethod = $method;
            } else {
                try {
                    $reflectionMethod = $this->getReflectionMethod($value, $method);
                } catch (\MolliePrefix\Symfony\Component\DependencyInjection\Exception\RuntimeException $e) {
                    if ($value->getFactory()) {
                        continue;
                    }
                    throw $e;
                }
            }
            foreach ($reflectionMethod->getParameters() as $key => $parameter) {
                if (\array_key_exists($key, $arguments) && '' !== $arguments[$key]) {
                    continue;
                }
                if (\array_key_exists('$' . $parameter->name, $bindings)) {
                    $arguments[$key] = $this->getBindingValue($bindings['$' . $parameter->name]);
                    continue;
                }
                $typeHint = \MolliePrefix\Symfony\Component\DependencyInjection\LazyProxy\ProxyHelper::getTypeHint($reflectionMethod, $parameter, \true);
                if (!isset($bindings[$typeHint])) {
                    continue;
                }
                $arguments[$key] = $this->getBindingValue($bindings[$typeHint]);
            }
            if ($arguments !== $call[1]) {
                \ksort($arguments);
                $calls[$i][1] = $arguments;
            }
        }
        if ($constructor) {
            list(, $arguments) = \array_pop($calls);
            if ($arguments !== $value->getArguments()) {
                $value->setArguments($arguments);
            }
        }
        if ($calls !== $value->getMethodCalls()) {
            $value->setMethodCalls($calls);
        }
        return parent::processValue($value, $isRoot);
    }
    private function getBindingValue(\MolliePrefix\Symfony\Component\DependencyInjection\Argument\BoundArgument $binding)
    {
        list($bindingValue, $bindingId) = $binding->getValues();
        $this->usedBindings[$bindingId] = \true;
        unset($this->unusedBindings[$bindingId]);
        return $bindingValue;
    }
}
