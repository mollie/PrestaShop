<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\DependencyInjection\Tests\Extension;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Extension\Extension;
class ExtensionTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getResolvedEnabledFixtures
     */
    public function testIsConfigEnabledReturnsTheResolvedValue($enabled)
    {
        $extension = new \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Extension\EnableableExtension();
        $this->assertSame($enabled, $extension->isConfigEnabled(new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder(), ['enabled' => $enabled]));
    }
    public function getResolvedEnabledFixtures()
    {
        return [[\true], [\false]];
    }
    public function testIsConfigEnabledOnNonEnableableConfig()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('The config array has no \'enabled\' key.');
        $extension = new \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Extension\EnableableExtension();
        $extension->isConfigEnabled(new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder(), []);
    }
}
class EnableableExtension extends \MolliePrefix\Symfony\Component\DependencyInjection\Extension\Extension
{
    public function load(array $configs, \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
    }
    public function isConfigEnabled(\MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder $container, array $config)
    {
        return parent::isConfigEnabled($container, $config);
    }
}
