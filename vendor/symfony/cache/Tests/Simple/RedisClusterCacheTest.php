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

class RedisClusterCacheTest extends \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Tests\Simple\AbstractRedisCacheTest
{
    public static function setUpBeforeClass()
    {
        if (!\class_exists('_PhpScoper5ea00cc67502b\\RedisCluster')) {
            self::markTestSkipped('The RedisCluster class is required.');
        }
        if (!($hosts = \getenv('REDIS_CLUSTER_HOSTS'))) {
            self::markTestSkipped('REDIS_CLUSTER_HOSTS env var is not defined.');
        }
        self::$redis = new \_PhpScoper5ea00cc67502b\RedisCluster(null, \explode(' ', $hosts));
    }
}
