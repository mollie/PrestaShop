<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Tests\Unit\Install;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Install\Installer;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;

class InstallerTest extends TestCase
{
    public function testInstallRegistersTheCheckoutTopHook(): void
    {
        $this->assertContains('displayPersonalInformationTop', Installer::getHooks());
    }

    public function testNewInstallsHaveExpressFullyOn(): void
    {
        $saved = [];
        $configuration = $this->createMock(ConfigurationAdapter::class);
        $configuration->method('updateValue')->willReturnCallback(function ($key, $value) use (&$saved) {
            if (!is_string($key)) {
                return;
            }

            $saved[$key] = $value;
        });

        $installer = $this->getMockBuilder(Installer::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $property = new ReflectionProperty(Installer::class, 'configurationAdapter');
        $property->setAccessible(true);
        $property->setValue($installer, $configuration);

        $initConfig = new ReflectionMethod(Installer::class, 'initConfig');
        $initConfig->setAccessible(true);
        $initConfig->invoke($installer);

        foreach ([
            Config::MOLLIE_EXPRESS_COMPONENT_ENABLED,
            Config::MOLLIE_EXPRESS_COMPONENT_APPLEPAY,
            Config::MOLLIE_EXPRESS_COMPONENT_GOOGLEPAY,
            Config::MOLLIE_EXPRESS_COMPONENT_PAYPAL,
            Config::MOLLIE_EXPRESS_COMPONENT_PLACEMENT_CART,
            Config::MOLLIE_EXPRESS_COMPONENT_PLACEMENT_CHECKOUT,
            Config::MOLLIE_EXPRESS_COMPONENT_PLACEMENT_PAYMENT,
        ] as $key) {
            $this->assertArrayHasKey($key, $saved, sprintf('%s is not set on install', $key));
            $this->assertSame(1, $saved[$key], sprintf('%s is not on after install', $key));
        }
    }
}
