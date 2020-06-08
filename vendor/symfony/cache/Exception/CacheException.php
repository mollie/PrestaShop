<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5eddef0da618a\Symfony\Component\Cache\Exception;

use _PhpScoper5eddef0da618a\Psr\Cache\CacheException as Psr6CacheInterface;
use _PhpScoper5eddef0da618a\Psr\SimpleCache\CacheException as SimpleCacheInterface;
class CacheException extends \Exception implements \_PhpScoper5eddef0da618a\Psr\Cache\CacheException, \_PhpScoper5eddef0da618a\Psr\SimpleCache\CacheException
{
}
