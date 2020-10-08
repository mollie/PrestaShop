<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Cache\Adapter;

use MolliePrefix\Doctrine\Common\Cache\CacheProvider;
use MolliePrefix\Symfony\Component\Cache\Traits\DoctrineTrait;
class DoctrineAdapter extends \MolliePrefix\Symfony\Component\Cache\Adapter\AbstractAdapter
{
    use DoctrineTrait;
    /**
     * @param string $namespace
     * @param int    $defaultLifetime
     */
    public function __construct(\MolliePrefix\Doctrine\Common\Cache\CacheProvider $provider, $namespace = '', $defaultLifetime = 0)
    {
        parent::__construct('', $defaultLifetime);
        $this->provider = $provider;
        $provider->setNamespace($namespace);
    }
}
