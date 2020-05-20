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

use _PhpScoper5ea00cc67502b\Redis;
use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Adapter\RedisAdapter;
use function error_get_last;
use function extension_loaded;
use function getenv;
use function str_replace;

abstract class AbstractRedisAdapterTest extends AdapterTestCase
{
    protected $skippedTests = ['testExpiration' => 'Testing expiration slows down the test suite', 'testHasItemReturnsFalseWhenDeferredItemIsExpired' => 'Testing expiration slows down the test suite', 'testDefaultLifeTime' => 'Testing expiration slows down the test suite'];
    protected static $redis;
    public function createCachePool($defaultLifetime = 0)
    {
        return new RedisAdapter(self::$redis, str_replace('\\', '.', __CLASS__), $defaultLifetime);
    }
    public static function setUpBeforeClass()
    {
        if (!extension_loaded('redis')) {
            self::markTestSkipped('Extension redis required.');
        }
        if (!@(new Redis())->connect(getenv('REDIS_HOST'))) {
            $e = error_get_last();
            self::markTestSkipped($e['message']);
        }
    }
    public static function tearDownAfterClass()
    {
        self::$redis = null;
    }
}
