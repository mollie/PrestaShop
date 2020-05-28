<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Config;

use _PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Config\ContainerParametersResource;
class ContainerParametersResourceTest extends \_PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase
{
    /** @var ContainerParametersResource */
    private $resource;
    protected function setUp()
    {
        $this->resource = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Config\ContainerParametersResource(['locales' => ['fr', 'en'], 'default_locale' => 'fr']);
    }
    public function testToString()
    {
        $this->assertSame('container_parameters_9893d3133814ab03cac3490f36dece77', (string) $this->resource);
    }
    public function testSerializeUnserialize()
    {
        $unserialized = \unserialize(\serialize($this->resource));
        $this->assertEquals($this->resource, $unserialized);
    }
    public function testGetParameters()
    {
        $this->assertSame(['locales' => ['fr', 'en'], 'default_locale' => 'fr'], $this->resource->getParameters());
    }
}
