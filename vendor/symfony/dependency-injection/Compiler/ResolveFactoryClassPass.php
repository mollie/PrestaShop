<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler;

use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Definition;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Exception\RuntimeException;
use function is_array;
use function sprintf;

/**
 * @author Maxime Steinhausser <maxime.steinhausser@gmail.com>
 */
class ResolveFactoryClassPass extends AbstractRecursivePass
{
    /**
     * {@inheritdoc}
     */
    protected function processValue($value, $isRoot = false)
    {
        if ($value instanceof Definition && is_array($factory = $value->getFactory()) && null === $factory[0]) {
            if (null === ($class = $value->getClass())) {
                throw new RuntimeException(sprintf('The "%s" service is defined to be created by a factory, but is missing the factory class. Did you forget to define the factory or service class?', $this->currentId));
            }
            $factory[0] = $class;
            $value->setFactory($factory);
        }
        return parent::processValue($value, $isRoot);
    }
}
