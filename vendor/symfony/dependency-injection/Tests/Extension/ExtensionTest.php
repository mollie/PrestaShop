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
class ExtensionTest extends TestCase
{
    /**
     * @dataProvider getResolvedEnabledFixtures
     */
    public function testIsConfigEnabledReturnsTheResolvedValue($enabled)
    {
        $extension = new EnableableExtension();
        $this->assertSame($enabled, $extension->isConfigEnabled(new ContainerBuilder(), ['enabled' => $enabled]));
    }
    public function getResolvedEnabledFixtures()
    {
        return [[true], [false]];
    }
    public function testIsConfigEnabledOnNonEnableableConfig()
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('The config array has no \'enabled\' key.');
        $extension = new EnableableExtension();
        $extension->isConfigEnabled(new ContainerBuilder(), []);
    }
}
class EnableableExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
    }
    public function isConfigEnabled(ContainerBuilder $container, array $config)
    {
        return parent::isConfigEnabled($container, $config);
    }
}
