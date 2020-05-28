<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ece82d7231e4\Symfony\Component\Cache\Simple;

use _PhpScoper5ece82d7231e4\Symfony\Component\Cache\Traits\MemcachedTrait;
class MemcachedCache extends \_PhpScoper5ece82d7231e4\Symfony\Component\Cache\Simple\AbstractCache
{
    use MemcachedTrait;
    protected $maxIdLength = 250;
    /**
     * @param string $namespace
     * @param int    $defaultLifetime
     */
    public function __construct(\_PhpScoper5ece82d7231e4\Memcached $client, $namespace = '', $defaultLifetime = 0)
    {
        $this->init($client, $namespace, $defaultLifetime);
    }
}
