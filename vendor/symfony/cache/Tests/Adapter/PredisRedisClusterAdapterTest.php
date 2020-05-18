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

use _PhpScoper5ea00cc67502b\Predis\Client;
use function explode;
use function getenv;

class PredisRedisClusterAdapterTest extends AbstractRedisAdapterTest
{
    public static function setUpBeforeClass()
    {
        if (!($hosts = getenv('REDIS_CLUSTER_HOSTS'))) {
            self::markTestSkipped('REDIS_CLUSTER_HOSTS env var is not defined.');
        }
        self::$redis = new Client(explode(' ', $hosts), ['cluster' => 'redis']);
    }
    public static function tearDownAfterClass()
    {
        self::$redis = null;
    }
}
