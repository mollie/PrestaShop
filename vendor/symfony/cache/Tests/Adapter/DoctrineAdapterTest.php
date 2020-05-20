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

use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Adapter\DoctrineAdapter;
use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Tests\Fixtures\ArrayCache;
/**
 * @group time-sensitive
 */
class DoctrineAdapterTest extends \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Tests\Adapter\AdapterTestCase
{
    protected $skippedTests = ['testDeferredSaveWithoutCommit' => 'Assumes a shared cache which ArrayCache is not.', 'testSaveWithoutExpire' => 'Assumes a shared cache which ArrayCache is not.', 'testNotUnserializable' => 'ArrayCache does not use serialize/unserialize'];
    public function createCachePool($defaultLifetime = 0)
    {
        return new \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Adapter\DoctrineAdapter(new \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Tests\Fixtures\ArrayCache($defaultLifetime), '', $defaultLifetime);
    }
}
