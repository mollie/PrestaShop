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

use _PhpScoper5eddef0da618a\PHPUnit\Framework\MockObject\MockObject;
use _PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\AdapterInterface;
use _PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\ArrayAdapter;
use _PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\ChainAdapter;
use _PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\FilesystemAdapter;
use _PhpScoper5eddef0da618a\Symfony\Component\Cache\PruneableInterface;
use _PhpScoper5eddef0da618a\Symfony\Component\Cache\Tests\Fixtures\ExternalAdapter;
/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 * @group time-sensitive
 */
class ChainAdapterTest extends \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Tests\Adapter\AdapterTestCase
{
    public function createCachePool($defaultLifetime = 0)
    {
        return new \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\ChainAdapter([new \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\ArrayAdapter($defaultLifetime), new \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Tests\Fixtures\ExternalAdapter(), new \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\FilesystemAdapter('', $defaultLifetime)], $defaultLifetime);
    }
    public function testEmptyAdaptersException()
    {
        $this->expectException('_PhpScoper5eddef0da618a\\Symfony\\Component\\Cache\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('At least one adapter must be specified.');
        new \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\ChainAdapter([]);
    }
    public function testInvalidAdapterException()
    {
        $this->expectException('_PhpScoper5eddef0da618a\\Symfony\\Component\\Cache\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('The class "stdClass" does not implement');
        new \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\ChainAdapter([new \stdClass()]);
    }
    public function testPrune()
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }
        $cache = new \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\ChainAdapter([$this->getPruneableMock(), $this->getNonPruneableMock(), $this->getPruneableMock()]);
        $this->assertTrue($cache->prune());
        $cache = new \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\ChainAdapter([$this->getPruneableMock(), $this->getFailingPruneableMock(), $this->getPruneableMock()]);
        $this->assertFalse($cache->prune());
    }
    /**
     * @return MockObject|PruneableCacheInterface
     */
    private function getPruneableMock()
    {
        $pruneable = $this->getMockBuilder(\_PhpScoper5eddef0da618a\Symfony\Component\Cache\Tests\Adapter\PruneableCacheInterface::class)->getMock();
        $pruneable->expects($this->atLeastOnce())->method('prune')->willReturn(\true);
        return $pruneable;
    }
    /**
     * @return MockObject|PruneableCacheInterface
     */
    private function getFailingPruneableMock()
    {
        $pruneable = $this->getMockBuilder(\_PhpScoper5eddef0da618a\Symfony\Component\Cache\Tests\Adapter\PruneableCacheInterface::class)->getMock();
        $pruneable->expects($this->atLeastOnce())->method('prune')->willReturn(\false);
        return $pruneable;
    }
    /**
     * @return MockObject|AdapterInterface
     */
    private function getNonPruneableMock()
    {
        return $this->getMockBuilder(\_PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\AdapterInterface::class)->getMock();
    }
}
interface PruneableCacheInterface extends \_PhpScoper5eddef0da618a\Symfony\Component\Cache\PruneableInterface, \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\AdapterInterface
{
}
