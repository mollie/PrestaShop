<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5eddef0da618a\Symfony\Component\Cache\Tests\Simple;

use _PhpScoper5eddef0da618a\PHPUnit\Framework\MockObject\MockObject;
use _PhpScoper5eddef0da618a\Psr\SimpleCache\CacheInterface;
use _PhpScoper5eddef0da618a\Symfony\Component\Cache\PruneableInterface;
use _PhpScoper5eddef0da618a\Symfony\Component\Cache\Simple\ArrayCache;
use _PhpScoper5eddef0da618a\Symfony\Component\Cache\Simple\ChainCache;
use _PhpScoper5eddef0da618a\Symfony\Component\Cache\Simple\FilesystemCache;
/**
 * @group time-sensitive
 */
class ChainCacheTest extends \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Tests\Simple\CacheTestCase
{
    public function createSimpleCache($defaultLifetime = 0)
    {
        return new \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Simple\ChainCache([new \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Simple\ArrayCache($defaultLifetime), new \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Simple\FilesystemCache('', $defaultLifetime)], $defaultLifetime);
    }
    public function testEmptyCachesException()
    {
        $this->expectException('_PhpScoper5eddef0da618a\\Symfony\\Component\\Cache\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('At least one cache must be specified.');
        new \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Simple\ChainCache([]);
    }
    public function testInvalidCacheException()
    {
        $this->expectException('_PhpScoper5eddef0da618a\\Symfony\\Component\\Cache\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('The class "stdClass" does not implement');
        new \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Simple\ChainCache([new \stdClass()]);
    }
    public function testPrune()
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }
        $cache = new \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Simple\ChainCache([$this->getPruneableMock(), $this->getNonPruneableMock(), $this->getPruneableMock()]);
        $this->assertTrue($cache->prune());
        $cache = new \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Simple\ChainCache([$this->getPruneableMock(), $this->getFailingPruneableMock(), $this->getPruneableMock()]);
        $this->assertFalse($cache->prune());
    }
    /**
     * @return MockObject|PruneableCacheInterface
     */
    private function getPruneableMock()
    {
        $pruneable = $this->getMockBuilder(\_PhpScoper5eddef0da618a\Symfony\Component\Cache\Tests\Simple\PruneableCacheInterface::class)->getMock();
        $pruneable->expects($this->atLeastOnce())->method('prune')->willReturn(\true);
        return $pruneable;
    }
    /**
     * @return MockObject|PruneableCacheInterface
     */
    private function getFailingPruneableMock()
    {
        $pruneable = $this->getMockBuilder(\_PhpScoper5eddef0da618a\Symfony\Component\Cache\Tests\Simple\PruneableCacheInterface::class)->getMock();
        $pruneable->expects($this->atLeastOnce())->method('prune')->willReturn(\false);
        return $pruneable;
    }
    /**
     * @return MockObject|CacheInterface
     */
    private function getNonPruneableMock()
    {
        return $this->getMockBuilder(\_PhpScoper5eddef0da618a\Psr\SimpleCache\CacheInterface::class)->getMock();
    }
}
interface PruneableCacheInterface extends \_PhpScoper5eddef0da618a\Symfony\Component\Cache\PruneableInterface, \_PhpScoper5eddef0da618a\Psr\SimpleCache\CacheInterface
{
}
