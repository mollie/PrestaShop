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

use MolliePrefix\Symfony\Component\Cache\Simple\ArrayCache;
/**
 * @group time-sensitive
 */
class ArrayCacheTest extends \MolliePrefix\Symfony\Component\Cache\Tests\Simple\CacheTestCase
{
    public function createSimpleCache($defaultLifetime = 0)
    {
        return new \MolliePrefix\Symfony\Component\Cache\Simple\ArrayCache($defaultLifetime);
    }
}
