<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ece82d7231e4\Symfony\Component\Cache\Tests\Simple;

use _PhpScoper5ece82d7231e4\Psr\SimpleCache\CacheInterface;
use _PhpScoper5ece82d7231e4\Symfony\Component\Cache\Simple\FilesystemCache;
/**
 * @group time-sensitive
 */
class FilesystemCacheTest extends \_PhpScoper5ece82d7231e4\Symfony\Component\Cache\Tests\Simple\CacheTestCase
{
    public function createSimpleCache($defaultLifetime = 0)
    {
        return new \_PhpScoper5ece82d7231e4\Symfony\Component\Cache\Simple\FilesystemCache('', $defaultLifetime);
    }
    protected function isPruned(\_PhpScoper5ece82d7231e4\Psr\SimpleCache\CacheInterface $cache, $name)
    {
        $getFileMethod = (new \ReflectionObject($cache))->getMethod('getFile');
        $getFileMethod->setAccessible(\true);
        return !\file_exists($getFileMethod->invoke($cache, $name));
    }
}
