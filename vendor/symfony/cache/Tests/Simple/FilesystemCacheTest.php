<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Tests\Simple;

use _PhpScoper5ea00cc67502b\Psr\SimpleCache\CacheInterface;
use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Simple\FilesystemCache;
use ReflectionObject;
use function file_exists;

/**
 * @group time-sensitive
 */
class FilesystemCacheTest extends CacheTestCase
{
    public function createSimpleCache($defaultLifetime = 0)
    {
        return new FilesystemCache('', $defaultLifetime);
    }
    protected function isPruned(CacheInterface $cache, $name)
    {
        $getFileMethod = (new ReflectionObject($cache))->getMethod('getFile');
        $getFileMethod->setAccessible(true);
        return !file_exists($getFileMethod->invoke($cache, $name));
    }
}
