<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Tests\Adapter;

use _PhpScoper5ea00cc67502b\Predis\Client;
use function getenv;

class PredisClusterAdapterTest extends AbstractRedisAdapterTest
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$redis = new Client([['host' => getenv('REDIS_HOST')]]);
    }
    public static function tearDownAfterClass()
    {
        self::$redis = null;
    }
}
