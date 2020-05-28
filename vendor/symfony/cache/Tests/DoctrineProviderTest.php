<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ece82d7231e4\Symfony\Component\Cache\Tests;

use _PhpScoper5ece82d7231e4\Doctrine\Common\Cache\CacheProvider;
use _PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase;
use _PhpScoper5ece82d7231e4\Symfony\Component\Cache\Adapter\ArrayAdapter;
use _PhpScoper5ece82d7231e4\Symfony\Component\Cache\DoctrineProvider;
class DoctrineProviderTest extends \_PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase
{
    public function testProvider()
    {
        $pool = new \_PhpScoper5ece82d7231e4\Symfony\Component\Cache\Adapter\ArrayAdapter();
        $cache = new \_PhpScoper5ece82d7231e4\Symfony\Component\Cache\DoctrineProvider($pool);
        $this->assertInstanceOf(\_PhpScoper5ece82d7231e4\Doctrine\Common\Cache\CacheProvider::class, $cache);
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
