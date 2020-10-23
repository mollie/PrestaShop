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

use MolliePrefix\Psr\SimpleCache\CacheInterface;
use MolliePrefix\Symfony\Component\Cache\Simple\PhpFilesCache;
/**
 * @group time-sensitive
 */
class PhpFilesCacheTest extends \MolliePrefix\Symfony\Component\Cache\Tests\Simple\CacheTestCase
{
    protected $skippedTests = ['testDefaultLifeTime' => 'PhpFilesCache does not allow configuring a default lifetime.'];
    public function createSimpleCache()
    {
        if (!\MolliePrefix\Symfony\Component\Cache\Simple\PhpFilesCache::isSupported()) {
            $this->markTestSkipped('OPcache extension is not enabled.');
        }
        return new \MolliePrefix\Symfony\Component\Cache\Simple\PhpFilesCache('sf-cache');
    }
    protected function isPruned(\MolliePrefix\Psr\SimpleCache\CacheInterface $cache, $name)
    {
        $getFileMethod = (new \ReflectionObject($cache))->getMethod('getFile');
        $getFileMethod->setAccessible(\true);
        return !\file_exists($getFileMethod->invoke($cache, $name));
    }
}
