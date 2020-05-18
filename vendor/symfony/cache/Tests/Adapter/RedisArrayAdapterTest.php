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

class RedisArrayAdapterTest extends \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Tests\Adapter\AbstractRedisAdapterTest
{
    public static function setUpBeforeClass()
    {
        parent::setupBeforeClass();
        if (!\class_exists('_PhpScoper5ea00cc67502b\\RedisArray')) {
            self::markTestSkipped('The RedisArray class is required.');
        }
        self::$redis = new \_PhpScoper5ea00cc67502b\RedisArray([\getenv('REDIS_HOST')], ['lazy_connect' => \true]);
    }
}
