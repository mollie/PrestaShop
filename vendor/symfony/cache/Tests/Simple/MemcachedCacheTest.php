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
class MemcachedCacheTest extends \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Tests\Simple\CacheTestCase
{
    protected $skippedTests = ['testSetTtl' => 'Testing expiration slows down the test suite', 'testSetMultipleTtl' => 'Testing expiration slows down the test suite', 'testDefaultLifeTime' => 'Testing expiration slows down the test suite'];
    protected static $client;
    public static function setUpBeforeClass()
    {
        if (!\_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Simple\MemcachedCache::isSupported()) {
            self::markTestSkipped('Extension memcached >=2.2.0 required.');
        }
        self::$client = \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Adapter\AbstractAdapter::createConnection('memcached://' . \getenv('MEMCACHED_HOST'));
        self::$client->get('foo');
        $code = self::$client->getResultCode();
        if (\_PhpScoper5ea00cc67502b\Memcached::RES_SUCCESS !== $code && \_PhpScoper5ea00cc67502b\Memcached::RES_NOTFOUND !== $code) {
            self::markTestSkipped('Memcached error: ' . \strtolower(self::$client->getResultMessage()));
        }
    }
    public function createSimpleCache($defaultLifetime = 0)
    {
        $client = $defaultLifetime ? \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Adapter\AbstractAdapter::createConnection('memcached://' . \getenv('MEMCACHED_HOST'), ['binary_protocol' => \false]) : self::$client;
        return new \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Simple\MemcachedCache($client, \str_replace('\\', '.', __CLASS__), $defaultLifetime);
    }
    public function testCreatePersistentConnectionShouldNotDupServerList()
    {
        $instance = \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Simple\MemcachedCache::createConnection('memcached://' . \getenv('MEMCACHED_HOST'), ['persistent_id' => 'persistent']);
        $this->assertCount(1, $instance->getServerList());
        $instance = \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Simple\MemcachedCache::createConnection('memcached://' . \getenv('MEMCACHED_HOST'), ['persistent_id' => 'persistent']);
        $this->assertCount(1, $instance->getServerList());
    }
    public function testOptions()
    {
        $client = \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Simple\MemcachedCache::createConnection([], ['libketama_compatible' => \false, 'distribution' => 'modula', 'compression' => \true, 'serializer' => 'php', 'hash' => 'md5']);
        $this->assertSame(\_PhpScoper5ea00cc67502b\Memcached::SERIALIZER_PHP, $client->getOption(\_PhpScoper5ea00cc67502b\Memcached::OPT_SERIALIZER));
        $this->assertSame(\_PhpScoper5ea00cc67502b\Memcached::HASH_MD5, $client->getOption(\_PhpScoper5ea00cc67502b\Memcached::OPT_HASH));
        $this->assertTrue($client->getOption(\_PhpScoper5ea00cc67502b\Memcached::OPT_COMPRESSION));
        $this->assertSame(0, $client->getOption(\_PhpScoper5ea00cc67502b\Memcached::OPT_LIBKETAMA_COMPATIBLE));
        $this->assertSame(\_PhpScoper5ea00cc67502b\Memcached::DISTRIBUTION_MODULA, $client->getOption(\_PhpScoper5ea00cc67502b\Memcached::OPT_DISTRIBUTION));
    }
    /**
     * @dataProvider provideBadOptions
     */
    public function testBadOptions($name, $value)
    {
        $this->expectException('ErrorException');
        $this->expectExceptionMessage('constant(): Couldn\'t find constant Memcached::');
        \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Simple\MemcachedCache::createConnection([], [$name => $value]);
    }
    public function provideBadOptions()
    {
        return [['foo', 'bar'], ['hash', 'zyx'], ['serializer', 'zyx'], ['distribution', 'zyx']];
    }
    public function testDefaultOptions()
    {
        $this->assertTrue(\_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Simple\MemcachedCache::isSupported());
        $client = \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Simple\MemcachedCache::createConnection([]);
        $this->assertTrue($client->getOption(\_PhpScoper5ea00cc67502b\Memcached::OPT_COMPRESSION));
        $this->assertSame(1, $client->getOption(\_PhpScoper5ea00cc67502b\Memcached::OPT_BINARY_PROTOCOL));
        $this->assertSame(1, $client->getOption(\_PhpScoper5ea00cc67502b\Memcached::OPT_LIBKETAMA_COMPATIBLE));
    }
    public function testOptionSerializer()
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\Cache\\Exception\\CacheException');
        $this->expectExceptionMessage('MemcachedAdapter: "serializer" option must be "php" or "igbinary".');
        if (!\_PhpScoper5ea00cc67502b\Memcached::HAVE_JSON) {
            $this->markTestSkipped('Memcached::HAVE_JSON required');
        }
        new \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Simple\MemcachedCache(\_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Simple\MemcachedCache::createConnection([], ['serializer' => 'json']));
    }
    /**
     * @dataProvider provideServersSetting
     */
    public function testServersSetting($dsn, $host, $port)
    {
        $client1 = \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Simple\MemcachedCache::createConnection($dsn);
        $client2 = \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Simple\MemcachedCache::createConnection([$dsn]);
        $client3 = \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Simple\MemcachedCache::createConnection([[$host, $port]]);
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
