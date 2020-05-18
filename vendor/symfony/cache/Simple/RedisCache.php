<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Simple;

use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Traits\RedisTrait;
use Predis\Client;
use Redis;
use RedisArray;
use RedisCluster;

class RedisCache extends AbstractCache
{
    use RedisTrait;
    /**
     * @param Redis|RedisArray|RedisCluster|Client $redisClient
     * @param string                                          $namespace
     * @param int                                             $defaultLifetime
     */
    public function __construct($redisClient, $namespace = '', $defaultLifetime = 0)
    {
        $this->init($redisClient, $namespace, $defaultLifetime);
    }
}
