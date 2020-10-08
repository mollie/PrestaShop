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

class RedisArrayAdapterTest extends \MolliePrefix\Symfony\Component\Cache\Tests\Adapter\AbstractRedisAdapterTest
{
    public static function setUpBeforeClass()
    {
        parent::setupBeforeClass();
        if (!\class_exists('MolliePrefix\\RedisArray')) {
            self::markTestSkipped('The RedisArray class is required.');
        }
        self::$redis = new \MolliePrefix\RedisArray([\getenv('REDIS_HOST')], ['lazy_connect' => \true]);
    }
}
