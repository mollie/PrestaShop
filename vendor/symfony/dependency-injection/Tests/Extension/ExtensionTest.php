<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Extension;

use _PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Extension\Extension;
class ExtensionTest extends \_PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getResolvedEnabledFixtures
     */
    public function testIsConfigEnabledReturnsTheResolvedValue($enabled)
    {
        $extension = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Extension\EnableableExtension();
        $this->assertSame($enabled, $extension->isConfigEnabled(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder(), ['enabled' => $enabled]));
    }
    public function getResolvedEnabledFixtures()
    {
        return [[\true], [\false]];
    }
    public function testIsConfigEnabledOnNonEnableableConfig()
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('The config array has no \'enabled\' key.');
        $extension = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Extension\EnableableExtension();
        $extension->isConfigEnabled(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder(), []);
    }
}
class EnableableExtension extends \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Extension\Extension
{
    public function load(array $configs, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
    }
    public function isConfigEnabled(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder $container, array $config)
    {
        return parent::isConfigEnabled($container, $config);
    }
}
