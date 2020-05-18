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

use _PhpScoper5ea00cc67502b\RedisArray;
use function class_exists;
use function getenv;

class RedisArrayAdapterTest extends AbstractRedisAdapterTest
{
    public static function setUpBeforeClass()
    {
        parent::setupBeforeClass();
        if (!class_exists('_PhpScoper5ea00cc67502b\\RedisArray')) {
            self::markTestSkipped('The RedisArray class is required.');
        }
        self::$redis = new RedisArray([getenv('REDIS_HOST')], ['lazy_connect' => true]);
    }
}
