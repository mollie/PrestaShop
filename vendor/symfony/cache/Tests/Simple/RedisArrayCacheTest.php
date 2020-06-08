<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5eddef0da618a\Symfony\Component\Cache\Tests\Simple;

class RedisArrayCacheTest extends \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Tests\Simple\AbstractRedisCacheTest
{
    public static function setUpBeforeClass()
    {
        parent::setupBeforeClass();
        if (!\class_exists('_PhpScoper5eddef0da618a\\RedisArray')) {
            self::markTestSkipped('The RedisArray class is required.');
        }
        self::$redis = new \_PhpScoper5eddef0da618a\RedisArray([\getenv('REDIS_HOST')], ['lazy_connect' => \true]);
    }
}
