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

namespace Mollie\Tests\Integration\Install;

use PHPUnit\Framework\TestCase;

class ModuleVersionCompliancyTest extends TestCase
{
    public function testItIsCompliantWithTheCurrentShop()
    {
        $this->assertTrue(\Module::getInstanceByName('mollie')->checkCompliancy());
    }

    /** @dataProvider supportedVersionsDataProvider */
    public function testItAcceptsSupportedVersion(string $version)
    {
        $range = \Module::getInstanceByName('mollie')->ps_versions_compliancy;

        $this->assertTrue(version_compare($version, $range['min'], '>='));
        $this->assertTrue(version_compare($version, $range['max'], '<='));
    }

    /** @dataProvider unsupportedVersionsDataProvider */
    public function testItRejectsUnsupportedVersion(string $version)
    {
        $range = \Module::getInstanceByName('mollie')->ps_versions_compliancy;

        $this->assertTrue(
            version_compare($version, $range['min'], '<') || version_compare($version, $range['max'], '>')
        );
    }

    public function supportedVersionsDataProvider()
    {
        return [['1.7.6.0'], ['1.7.8.11'], ['8.2.3'], ['9.1.5'], ['9.2.0'], ['9.99.99']];
    }

    public function unsupportedVersionsDataProvider()
    {
        return [['1.7.5.3'], ['10.0.0']];
    }
}
