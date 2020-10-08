<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Cache\Tests\Adapter;

use MolliePrefix\Symfony\Component\Cache\Adapter\ArrayAdapter;
use MolliePrefix\Symfony\Component\Cache\Adapter\ProxyAdapter;
/**
 * @group time-sensitive
 */
class NamespacedProxyAdapterTest extends \MolliePrefix\Symfony\Component\Cache\Tests\Adapter\ProxyAdapterTest
{
    public function createCachePool($defaultLifetime = 0)
    {
        return new \MolliePrefix\Symfony\Component\Cache\Adapter\ProxyAdapter(new \MolliePrefix\Symfony\Component\Cache\Adapter\ArrayAdapter($defaultLifetime), 'foo', $defaultLifetime);
    }
}
