<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Tests\Adapter;

use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Adapter\ArrayAdapter;
use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Adapter\ProxyAdapter;
/**
 * @group time-sensitive
 */
class NamespacedProxyAdapterTest extends \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Tests\Adapter\ProxyAdapterTest
{
    public function createCachePool($defaultLifetime = 0)
    {
        return new \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Adapter\ProxyAdapter(new \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Adapter\ArrayAdapter($defaultLifetime), 'foo', $defaultLifetime);
    }
}
