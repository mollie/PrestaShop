<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ece82d7231e4\Symfony\Component\Cache\Exception;

use _PhpScoper5ece82d7231e4\Psr\Cache\InvalidArgumentException as Psr6CacheInterface;
use _PhpScoper5ece82d7231e4\Psr\SimpleCache\InvalidArgumentException as SimpleCacheInterface;
class InvalidArgumentException extends \InvalidArgumentException implements \_PhpScoper5ece82d7231e4\Psr\Cache\InvalidArgumentException, \_PhpScoper5ece82d7231e4\Psr\SimpleCache\InvalidArgumentException
{
}
