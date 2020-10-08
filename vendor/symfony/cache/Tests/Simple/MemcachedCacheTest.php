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
class MemcachedCacheTest extends \MolliePrefix\Symfony\Component\Cache\Tests\Simple\CacheTestCase
{
    protected $skippedTests = ['testSetTtl' => 'Testing expiration slows down the test suite', 'testSetMultipleTtl' => 'Testing expiration slows down the test suite', 'testDefaultLifeTime' => 'Testing expiration slows down the test suite'];
    protected static $client;
    public static function setUpBeforeClass()
    {
        if (!\MolliePrefix\Symfony\Component\Cache\Simple\MemcachedCache::isSupported()) {
            self::markTestSkipped('Extension memcached >=2.2.0 required.');
        }
        self::$client = \MolliePrefix\Symfony\Component\Cache\Adapter\AbstractAdapter::createConnection('memcached://' . \getenv('MEMCACHED_HOST'));
        self::$client->get('foo');
        $code = self::$client->getResultCode();
        if (\MolliePrefix\Memcached::RES_SUCCESS !== $code && \MolliePrefix\Memcached::RES_NOTFOUND !== $code) {
            self::markTestSkipped('Memcached error: ' . \strtolower(self::$client->getResultMessage()));
        }
    }
    public function createSimpleCache($defaultLifetime = 0)
    {
        $client = $defaultLifetime ? \MolliePrefix\Symfony\Component\Cache\Adapter\AbstractAdapter::createConnection('memcached://' . \getenv('MEMCACHED_HOST'), ['binary_protocol' => \false]) : self::$client;
        return new \MolliePrefix\Symfony\Component\Cache\Simple\MemcachedCache($client, \str_replace('\\', '.', __CLASS__), $defaultLifetime);
    }
    public function testCreatePersistentConnectionShouldNotDupServerList()
    {
        $instance = \MolliePrefix\Symfony\Component\Cache\Simple\MemcachedCache::createConnection('memcached://' . \getenv('MEMCACHED_HOST'), ['persistent_id' => 'persistent']);
        $this->assertCount(1, $instance->getServerList());
        $instance = \MolliePrefix\Symfony\Component\Cache\Simple\MemcachedCache::createConnection('memcached://' . \getenv('MEMCACHED_HOST'), ['persistent_id' => 'persistent']);
        $this->assertCount(1, $instance->getServerList());
    }
    public function testOptions()
    {
        $client = \MolliePrefix\Symfony\Component\Cache\Simple\MemcachedCache::createConnection([], ['libketama_compatible' => \false, 'distribution' => 'modula', 'compression' => \true, 'serializer' => 'php', 'hash' => 'md5']);
        $this->assertSame(\MolliePrefix\Memcached::SERIALIZER_PHP, $client->getOption(\MolliePrefix\Memcached::OPT_SERIALIZER));
        $this->assertSame(\MolliePrefix\Memcached::HASH_MD5, $client->getOption(\MolliePrefix\Memcached::OPT_HASH));
        $this->assertTrue($client->getOption(\MolliePrefix\Memcached::OPT_COMPRESSION));
        $this->assertSame(0, $client->getOption(\MolliePrefix\Memcached::OPT_LIBKETAMA_COMPATIBLE));
        $this->assertSame(\MolliePrefix\Memcached::DISTRIBUTION_MODULA, $client->getOption(\MolliePrefix\Memcached::OPT_DISTRIBUTION));
    }
    /**
     * @dataProvider provideBadOptions
     */
    public function testBadOptions($name, $value)
    {
        if (\PHP_VERSION_ID < 80000) {
            $this->expectException('ErrorException');
            $this->expectExceptionMessage('constant(): Couldn\'t find constant Memcached::');
        } else {
            $this->expectException('Error');
            $this->expectExceptionMessage('Undefined constant Memcached::');
        }
        \MolliePrefix\Symfony\Component\Cache\Simple\MemcachedCache::createConnection([], [$name => $value]);
    }
    public function provideBadOptions()
    {
        return [['foo', 'bar'], ['hash', 'zyx'], ['serializer', 'zyx'], ['distribution', 'zyx']];
    }
    public function testDefaultOptions()
    {
        $this->assertTrue(\MolliePrefix\Symfony\Component\Cache\Simple\MemcachedCache::isSupported());
        $client = \MolliePrefix\Symfony\Component\Cache\Simple\MemcachedCache::createConnection([]);
        $this->assertTrue($client->getOption(\MolliePrefix\Memcached::OPT_COMPRESSION));
        $this->assertSame(1, $client->getOption(\MolliePrefix\Memcached::OPT_BINARY_PROTOCOL));
        $this->assertSame(1, $client->getOption(\MolliePrefix\Memcached::OPT_LIBKETAMA_COMPATIBLE));
    }
    public function testOptionSerializer()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\Cache\\Exception\\CacheException');
        $this->expectExceptionMessage('MemcachedAdapter: "serializer" option must be "php" or "igbinary".');
        if (!\MolliePrefix\Memcached::HAVE_JSON) {
            $this->markTestSkipped('Memcached::HAVE_JSON required');
        }
        new \MolliePrefix\Symfony\Component\Cache\Simple\MemcachedCache(\MolliePrefix\Symfony\Component\Cache\Simple\MemcachedCache::createConnection([], ['serializer' => 'json']));
    }
    /**
     * @dataProvider provideServersSetting
     */
    public function testServersSetting($dsn, $host, $port)
    {
        $client1 = \MolliePrefix\Symfony\Component\Cache\Simple\MemcachedCache::createConnection($dsn);
        $client2 = \MolliePrefix\Symfony\Component\Cache\Simple\MemcachedCache::createConnection([$dsn]);
        $client3 = \MolliePrefix\Symfony\Component\Cache\Simple\MemcachedCache::createConnection([[$host, $port]]);
        $expect = ['host' => $host, 'port' => $port];
        $f = function ($s) {
            return ['host' => $s['host'], 'port' => $s['port']];
        };
        $this->assertSame([$expect], \array_map($f, $client1->getServerList()));
        $this->assertSame([$expect], \array_map($f, $client2->getServerList()));
        $this->assertSame([$expect], \array_map($f, $client3->getServerList()));
    }
    public function provideServersSetting()
    {
        (yield ['memcached://127.0.0.1/50', '127.0.0.1', 11211]);
        (yield ['memcached://localhost:11222?weight=25', 'localhost', 11222]);
        if (\filter_var(\ini_get('memcached.use_sasl'), \FILTER_VALIDATE_BOOLEAN)) {
            (yield ['memcached://user:password@127.0.0.1?weight=50', '127.0.0.1', 11211]);
        }
        (yield ['memcached:///var/run/memcached.sock?weight=25', '/var/run/memcached.sock', 0]);
        (yield ['memcached:///var/local/run/memcached.socket?weight=25', '/var/local/run/memcached.socket', 0]);
        if (\filter_var(\ini_get('memcached.use_sasl'), \FILTER_VALIDATE_BOOLEAN)) {
            (yield ['memcached://user:password@/var/local/run/memcached.socket?weight=25', '/var/local/run/memcached.socket', 0]);
        }
    }
}
