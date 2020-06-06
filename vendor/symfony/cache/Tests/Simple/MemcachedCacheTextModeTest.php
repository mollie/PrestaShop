<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Tests\Simple;

use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Adapter\AbstractAdapter;
use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Simple\MemcachedCache;
class MemcachedCacheTextModeTest extends \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Tests\Simple\MemcachedCacheTest
{
    public function createSimpleCache($defaultLifetime = 0)
    {
        $client = \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Adapter\AbstractAdapter::createConnection('memcached://' . \getenv('MEMCACHED_HOST'), ['binary_protocol' => \false]);
        return new \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Simple\MemcachedCache($client, \str_replace('\\', '.', __CLASS__), $defaultLifetime);
    }
}
