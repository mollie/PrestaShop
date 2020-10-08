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

use MolliePrefix\Symfony\Component\Cache\Adapter\AbstractAdapter;
use MolliePrefix\Symfony\Component\Cache\Adapter\RedisAdapter;
use MolliePrefix\Symfony\Component\Cache\Traits\RedisProxy;
class RedisAdapterTest extends \MolliePrefix\Symfony\Component\Cache\Tests\Adapter\AbstractRedisAdapterTest
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$redis = \MolliePrefix\Symfony\Component\Cache\Adapter\AbstractAdapter::createConnection('redis://' . \getenv('REDIS_HOST'), ['lazy' => \true]);
    }
    public function createCachePool($defaultLifetime = 0)
    {
        $adapter = parent::createCachePool($defaultLifetime);
        $this->assertInstanceOf(\MolliePrefix\Symfony\Component\Cache\Traits\RedisProxy::class, self::$redis);
        return $adapter;
    }
    public function testCreateConnection()
    {
        $redisHost = \getenv('REDIS_HOST');
        $redis = \MolliePrefix\Symfony\Component\Cache\Adapter\RedisAdapter::createConnection('redis://' . $redisHost);
        $this->assertInstanceOf(\MolliePrefix\Redis::class, $redis);
        $this->assertTrue($redis->isConnected());
        $this->assertSame(0, $redis->getDbNum());
        $redis = \MolliePrefix\Symfony\Component\Cache\Adapter\RedisAdapter::createConnection('redis://' . $redisHost . '/2');
        $this->assertSame(2, $redis->getDbNum());
        $redis = \MolliePrefix\Symfony\Component\Cache\Adapter\RedisAdapter::createConnection('redis://' . $redisHost, ['timeout' => 3]);
        $this->assertEquals(3, $redis->getTimeout());
        $redis = \MolliePrefix\Symfony\Component\Cache\Adapter\RedisAdapter::createConnection('redis://' . $redisHost . '?timeout=4');
        $this->assertEquals(4, $redis->getTimeout());
        $redis = \MolliePrefix\Symfony\Component\Cache\Adapter\RedisAdapter::createConnection('redis://' . $redisHost, ['read_timeout' => 5]);
        $this->assertEquals(5, $redis->getReadTimeout());
    }
    /**
     * @dataProvider provideFailedCreateConnection
     */
    public function testFailedCreateConnection($dsn)
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\Cache\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('Redis connection ');
        \MolliePrefix\Symfony\Component\Cache\Adapter\RedisAdapter::createConnection($dsn);
    }
    public function provideFailedCreateConnection()
    {
        return [['redis://localhost:1234'], ['redis://foo@localhost'], ['redis://localhost/123']];
    }
    /**
     * @dataProvider provideInvalidCreateConnection
     */
    public function testInvalidCreateConnection($dsn)
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\Cache\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid Redis DSN');
        \MolliePrefix\Symfony\Component\Cache\Adapter\RedisAdapter::createConnection($dsn);
    }
    public function provideInvalidCreateConnection()
    {
        return [['foo://localhost'], ['redis://']];
    }
}
