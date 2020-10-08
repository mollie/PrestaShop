<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\DependencyInjection\LazyProxy\PhpDumper;

use MolliePrefix\Symfony\Component\DependencyInjection\Definition;
/**
 * Null dumper, negates any proxy code generation for any given service definition.
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 *
 * @final since version 3.3
 */
class NullDumper implements \MolliePrefix\Symfony\Component\DependencyInjection\LazyProxy\PhpDumper\DumperInterface
{
    /**
     * {@inheritdoc}
     */
    public function isProxyCandidate(\MolliePrefix\Symfony\Component\DependencyInjection\Definition $definition)
    {
        return \false;
    }
    /**
     * {@inheritdoc}
     */
    public function getProxyFactoryCode(\MolliePrefix\Symfony\Component\DependencyInjection\Definition $definition, $id, $factoryCode = null)
    {
        return '';
    }
    /**
     * {@inheritdoc}
     */
    public function getProxyCode(\MolliePrefix\Symfony\Component\DependencyInjection\Definition $definition)
    {
        return '';
    }
}
