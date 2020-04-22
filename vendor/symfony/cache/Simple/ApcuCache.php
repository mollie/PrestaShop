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

use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Traits\ApcuTrait;
class ApcuCache extends \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Simple\AbstractCache
{
    use ApcuTrait;
    /**
     * @param string      $namespace
     * @param int         $defaultLifetime
     * @param string|null $version
     */
    public function __construct($namespace = '', $defaultLifetime = 0, $version = null)
    {
        $this->init($namespace, $defaultLifetime, $version);
    }
}
