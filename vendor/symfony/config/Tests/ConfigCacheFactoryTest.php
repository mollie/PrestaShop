<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Config\Tests;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Config\ConfigCacheFactory;
class ConfigCacheFactoryTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testCacheWithInvalidCallback()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Invalid type for callback argument. Expected callable, but got "object".');
        $cacheFactory = new \MolliePrefix\Symfony\Component\Config\ConfigCacheFactory(\true);
        $cacheFactory->cache('file', new \stdClass());
    }
}
