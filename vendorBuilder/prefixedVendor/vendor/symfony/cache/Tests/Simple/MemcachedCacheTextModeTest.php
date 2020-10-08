<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Cache\Tests\Simple;

use MolliePrefix\Symfony\Component\Cache\Adapter\AbstractAdapter;
use MolliePrefix\Symfony\Component\Cache\Simple\MemcachedCache;
class MemcachedCacheTextModeTest extends \MolliePrefix\Symfony\Component\Cache\Tests\Simple\MemcachedCacheTest
{
    public function createSimpleCache($defaultLifetime = 0)
    {
        $client = \MolliePrefix\Symfony\Component\Cache\Adapter\AbstractAdapter::createConnection('memcached://' . \getenv('MEMCACHED_HOST'), ['binary_protocol' => \false]);
        return new \MolliePrefix\Symfony\Component\Cache\Simple\MemcachedCache($client, \str_replace('\\', '.', __CLASS__), $defaultLifetime);
    }
}
