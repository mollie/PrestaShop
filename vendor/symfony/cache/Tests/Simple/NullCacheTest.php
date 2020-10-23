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

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Cache\Simple\NullCache;
/**
 * @group time-sensitive
 */
class NullCacheTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function createCachePool()
    {
        return new \MolliePrefix\Symfony\Component\Cache\Simple\NullCache();
    }
    public function testGetItem()
    {
        $cache = $this->createCachePool();
        $this->assertNull($cache->get('key'));
    }
    public function testHas()
    {
        $this->assertFalse($this->createCachePool()->has('key'));
    }
    public function testGetMultiple()
    {
        $cache = $this->createCachePool();
        $keys = ['foo', 'bar', 'baz', 'biz'];
        $default = new \stdClass();
        $items = $cache->getMultiple($keys, $default);
        $count = 0;
        foreach ($items as $key => $item) {
            $this->assertContains($key, $keys, 'Cache key can not change.');
            $this->assertSame($default, $item);
            // Remove $key for $keys
            foreach ($keys as $k => $v) {
                if ($v === $key) {
                    unset($keys[$k]);
                }
            }
            ++$count;
        }
        $this->assertSame(4, $count);
    }
    public function testClear()
    {
        $this->assertTrue($this->createCachePool()->clear());
    }
    public function testDelete()
    {
        $this->assertTrue($this->createCachePool()->delete('key'));
    }
    public function testDeleteMultiple()
    {
        $this->assertTrue($this->createCachePool()->deleteMultiple(['key', 'foo', 'bar']));
    }
    public function testSet()
    {
        $cache = $this->createCachePool();
        $this->assertFalse($cache->set('key', 'val'));
        $this->assertNull($cache->get('key'));
    }
    public function testSetMultiple()
    {
        $cache = $this->createCachePool();
        $this->assertFalse($cache->setMultiple(['key' => 'val']));
        $this->assertNull($cache->get('key'));
    }
}
