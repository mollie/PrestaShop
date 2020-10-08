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

class RedisClusterAdapterTest extends \MolliePrefix\Symfony\Component\Cache\Tests\Adapter\AbstractRedisAdapterTest
{
    public static function setUpBeforeClass()
    {
        if (!\class_exists('MolliePrefix\\RedisCluster')) {
            self::markTestSkipped('The RedisCluster class is required.');
        }
        if (!($hosts = \getenv('REDIS_CLUSTER_HOSTS'))) {
            self::markTestSkipped('REDIS_CLUSTER_HOSTS env var is not defined.');
        }
        self::$redis = new \MolliePrefix\RedisCluster(null, \explode(' ', $hosts));
    }
}
