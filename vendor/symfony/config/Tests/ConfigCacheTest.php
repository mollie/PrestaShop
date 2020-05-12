<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\Config\Tests;

use _PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase;
use _PhpScoper5ea00cc67502b\Symfony\Component\Config\ConfigCache;
use _PhpScoper5ea00cc67502b\Symfony\Component\Config\Tests\Resource\ResourceStub;
class ConfigCacheTest extends \_PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase
{
    private $cacheFile = null;
    protected function setUp()
    {
        $this->cacheFile = \tempnam(\sys_get_temp_dir(), 'config_');
    }
    protected function tearDown()
    {
        $files = [$this->cacheFile, $this->cacheFile . '.meta'];
        foreach ($files as $file) {
            if (\file_exists($file)) {
                \unlink($file);
            }
        }
    }
    /**
     * @dataProvider debugModes
     */
    public function testCacheIsNotValidIfNothingHasBeenCached($debug)
    {
        \unlink($this->cacheFile);
        // remove tempnam() side effect
        $cache = new \_PhpScoper5ea00cc67502b\Symfony\Component\Config\ConfigCache($this->cacheFile, $debug);
        $this->assertFalse($cache->isFresh());
    }
    public function testIsAlwaysFreshInProduction()
    {
        $staleResource = new \_PhpScoper5ea00cc67502b\Symfony\Component\Config\Tests\Resource\ResourceStub();
        $staleResource->setFresh(\false);
        $cache = new \_PhpScoper5ea00cc67502b\Symfony\Component\Config\ConfigCache($this->cacheFile, \false);
        $cache->write('', [$staleResource]);
        $this->assertTrue($cache->isFresh());
    }
    /**
     * @dataProvider debugModes
     */
    public function testIsFreshWhenNoResourceProvided($debug)
    {
        $cache = new \_PhpScoper5ea00cc67502b\Symfony\Component\Config\ConfigCache($this->cacheFile, $debug);
        $cache->write('', []);
        $this->assertTrue($cache->isFresh());
    }
    public function testFreshResourceInDebug()
    {
        $freshResource = new \_PhpScoper5ea00cc67502b\Symfony\Component\Config\Tests\Resource\ResourceStub();
        $freshResource->setFresh(\true);
        $cache = new \_PhpScoper5ea00cc67502b\Symfony\Component\Config\ConfigCache($this->cacheFile, \true);
        $cache->write('', [$freshResource]);
        $this->assertTrue($cache->isFresh());
    }
    public function testStaleResourceInDebug()
    {
        $staleResource = new \_PhpScoper5ea00cc67502b\Symfony\Component\Config\Tests\Resource\ResourceStub();
        $staleResource->setFresh(\false);
        $cache = new \_PhpScoper5ea00cc67502b\Symfony\Component\Config\ConfigCache($this->cacheFile, \true);
        $cache->write('', [$staleResource]);
        $this->assertFalse($cache->isFresh());
    }
    public function debugModes()
    {
        return [[\true], [\false]];
    }
}
