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

    /** @dataProvider versionsDataProvider */
    public function testItSupportsOnlyVersionsInsideTheDeclaredRange(string $version, bool $supported)
    {
        $range = \Module::getInstanceByName('mollie')->ps_versions_compliancy;

        $insideRange = version_compare($version, $range['min'], '>=') && version_compare($version, $range['max'], '<=');

        $this->assertSame($supported, $insideRange);
    }

    public function versionsDataProvider()
    {
        return [
            ['1.7.5.3', false],
            ['1.7.6.0', true],
            ['1.7.8.11', true],
            ['8.2.3', true],
            ['9.1.5', true],
            ['9.2.0', true],
            ['9.99.99', true],
            ['10.0.0', false],
        ];
    }
}
