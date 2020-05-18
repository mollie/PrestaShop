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

use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Simple\DoctrineCache;
use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Tests\Fixtures\ArrayCache;
/**
 * @group time-sensitive
 */
class DoctrineCacheTest extends CacheTestCase
{
    protected $skippedTests = ['testObjectDoesNotChangeInCache' => 'ArrayCache does not use serialize/unserialize', 'testNotUnserializable' => 'ArrayCache does not use serialize/unserialize'];
    public function createSimpleCache($defaultLifetime = 0)
    {
        return new DoctrineCache(new ArrayCache($defaultLifetime), '', $defaultLifetime);
    }
}
