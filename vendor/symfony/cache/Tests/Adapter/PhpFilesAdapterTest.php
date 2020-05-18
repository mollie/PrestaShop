<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Tests\Adapter;

use _PhpScoper5ea00cc67502b\Psr\Cache\CacheItemPoolInterface;
use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use ReflectionObject;
use function file_exists;
use function sys_get_temp_dir;

/**
 * @group time-sensitive
 */
class PhpFilesAdapterTest extends AdapterTestCase
{
    protected $skippedTests = ['testDefaultLifeTime' => 'PhpFilesAdapter does not allow configuring a default lifetime.'];
    public function createCachePool()
    {
        if (!PhpFilesAdapter::isSupported()) {
            $this->markTestSkipped('OPcache extension is not enabled.');
        }
        return new PhpFilesAdapter('sf-cache');
    }
    public static function tearDownAfterClass()
    {
        FilesystemAdapterTest::rmdir(sys_get_temp_dir() . '/symfony-cache');
    }
    protected function isPruned(CacheItemPoolInterface $cache, $name)
    {
        $getFileMethod = (new ReflectionObject($cache))->getMethod('getFile');
        $getFileMethod->setAccessible(true);
        return !file_exists($getFileMethod->invoke($cache, $name));
    }
}
