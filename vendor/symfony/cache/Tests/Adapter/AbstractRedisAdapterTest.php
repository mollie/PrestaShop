<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5eddef0da618a\Symfony\Component\Cache\Tests\Adapter;

use _PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\RedisAdapter;
abstract class AbstractRedisAdapterTest extends \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Tests\Adapter\AdapterTestCase
{
    protected $skippedTests = ['testExpiration' => 'Testing expiration slows down the test suite', 'testHasItemReturnsFalseWhenDeferredItemIsExpired' => 'Testing expiration slows down the test suite', 'testDefaultLifeTime' => 'Testing expiration slows down the test suite'];
    protected static $redis;
    public function createCachePool($defaultLifetime = 0)
    {
        return new \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\RedisAdapter(self::$redis, \str_replace('\\', '.', __CLASS__), $defaultLifetime);
    }
    public static function setUpBeforeClass()
    {
        if (!\extension_loaded('redis')) {
            self::markTestSkipped('Extension redis required.');
        }
        if (!@(new \_PhpScoper5eddef0da618a\Redis())->connect(\getenv('REDIS_HOST'))) {
            $e = \error_get_last();
            self::markTestSkipped($e['message']);
        }
    }
    public static function tearDownAfterClass()
    {
        self::$redis = null;
    }
}
