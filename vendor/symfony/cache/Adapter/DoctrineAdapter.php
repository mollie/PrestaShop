<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ece82d7231e4\Symfony\Component\Cache\Adapter;

use _PhpScoper5ece82d7231e4\Doctrine\Common\Cache\CacheProvider;
use _PhpScoper5ece82d7231e4\Symfony\Component\Cache\Traits\DoctrineTrait;
class DoctrineAdapter extends \_PhpScoper5ece82d7231e4\Symfony\Component\Cache\Adapter\AbstractAdapter
{
    use DoctrineTrait;
    /**
     * @param string $namespace
     * @param int    $defaultLifetime
     */
    public function __construct(\_PhpScoper5ece82d7231e4\Doctrine\Common\Cache\CacheProvider $provider, $namespace = '', $defaultLifetime = 0)
    {
        parent::__construct('', $defaultLifetime);
        $this->provider = $provider;
        $provider->setNamespace($namespace);
    }
}
