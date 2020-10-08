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

use MolliePrefix\Psr\Cache\CacheItemPoolInterface;
use MolliePrefix\Symfony\Component\Cache\Adapter\PhpFilesAdapter;
/**
 * @group time-sensitive
 */
class PhpFilesAdapterTest extends \MolliePrefix\Symfony\Component\Cache\Tests\Adapter\AdapterTestCase
{
    protected $skippedTests = ['testDefaultLifeTime' => 'PhpFilesAdapter does not allow configuring a default lifetime.'];
    public function createCachePool()
    {
        if (!\MolliePrefix\Symfony\Component\Cache\Adapter\PhpFilesAdapter::isSupported()) {
            $this->markTestSkipped('OPcache extension is not enabled.');
        }
        return new \MolliePrefix\Symfony\Component\Cache\Adapter\PhpFilesAdapter('sf-cache');
    }
    public static function tearDownAfterClass()
    {
        \MolliePrefix\Symfony\Component\Cache\Tests\Adapter\FilesystemAdapterTest::rmdir(\sys_get_temp_dir() . '/symfony-cache');
    }
    protected function isPruned(\MolliePrefix\Psr\Cache\CacheItemPoolInterface $cache, $name)
    {
        $getFileMethod = (new \ReflectionObject($cache))->getMethod('getFile');
        $getFileMethod->setAccessible(\true);
        return !\file_exists($getFileMethod->invoke($cache, $name));
    }
}
