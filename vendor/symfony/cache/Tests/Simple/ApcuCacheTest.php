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

use _PhpScoper5eddef0da618a\Symfony\Component\Cache\Simple\ApcuCache;
class ApcuCacheTest extends \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Tests\Simple\CacheTestCase
{
    protected $skippedTests = ['testSetTtl' => 'Testing expiration slows down the test suite', 'testSetMultipleTtl' => 'Testing expiration slows down the test suite', 'testDefaultLifeTime' => 'Testing expiration slows down the test suite'];
    public function createSimpleCache($defaultLifetime = 0)
    {
        if (!\function_exists('_PhpScoper5eddef0da618a\\apcu_fetch') || !\filter_var(\ini_get('apc.enabled'), \FILTER_VALIDATE_BOOLEAN) || 'cli' === \PHP_SAPI && !\filter_var(\ini_get('apc.enable_cli'), \FILTER_VALIDATE_BOOLEAN)) {
            $this->markTestSkipped('APCu extension is required.');
        }
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $this->markTestSkipped('Fails transiently on Windows.');
        }
        return new \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Simple\ApcuCache(\str_replace('\\', '.', __CLASS__), $defaultLifetime);
    }
}
