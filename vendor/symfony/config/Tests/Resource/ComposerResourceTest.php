<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Config\Tests\Resource;

use MolliePrefix\Composer\Autoload\ClassLoader;
use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Config\Resource\ComposerResource;
class ComposerResourceTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testGetVendor()
    {
        $res = new \MolliePrefix\Symfony\Component\Config\Resource\ComposerResource();
        $r = new \ReflectionClass(\MolliePrefix\Composer\Autoload\ClassLoader::class);
        $found = \false;
        foreach ($res->getVendors() as $vendor) {
            if ($vendor && 0 === \strpos($r->getFileName(), $vendor)) {
                $found = \true;
                break;
            }
        }
        $this->assertTrue($found);
    }
    public function testSerializeUnserialize()
    {
        $res = new \MolliePrefix\Symfony\Component\Config\Resource\ComposerResource();
        $ser = \unserialize(\serialize($res));
        $this->assertTrue($res->isFresh(0));
        $this->assertTrue($ser->isFresh(0));
        $this->assertEquals($res, $ser);
    }
}
