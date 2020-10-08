<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Cache\Tests;

use MolliePrefix\Doctrine\Common\Cache\CacheProvider;
use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Cache\Adapter\ArrayAdapter;
use MolliePrefix\Symfony\Component\Cache\DoctrineProvider;
class DoctrineProviderTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testProvider()
    {
        $pool = new \MolliePrefix\Symfony\Component\Cache\Adapter\ArrayAdapter();
        $cache = new \MolliePrefix\Symfony\Component\Cache\DoctrineProvider($pool);
        $this->assertInstanceOf(\MolliePrefix\Doctrine\Common\Cache\CacheProvider::class, $cache);
        $key = '{}()/\\@:';
        $this->assertTrue($cache->delete($key));
        $this->assertFalse($cache->contains($key));
        $this->assertTrue($cache->save($key, 'bar'));
        $this->assertTrue($cache->contains($key));
        $this->assertSame('bar', $cache->fetch($key));
        $this->assertTrue($cache->delete($key));
        $this->assertFalse($cache->fetch($key));
        $this->assertTrue($cache->save($key, 'bar'));
        $cache->flushAll();
        $this->assertFalse($cache->fetch($key));
        $this->assertFalse($cache->contains($key));
    }
}
