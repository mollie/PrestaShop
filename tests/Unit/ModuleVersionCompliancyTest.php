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

namespace Mollie\Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../mollie.php';

class ModuleVersionCompliancyTest extends TestCase
{
    public function testItAcceptsEveryVersionOfTheLatestSupportedMajor()
    {
        $range = (new \Mollie())->ps_versions_compliancy;

        $this->assertTrue(version_compare('1.7.6.0', $range['min'], '>='));
        $this->assertTrue(version_compare('9.99.99', $range['max'], '<='));
    }

    public function testItRejectsVersionsOutsideTheSupportedRange()
    {
        $range = (new \Mollie())->ps_versions_compliancy;

        $this->assertTrue(version_compare('1.7.5.3', $range['min'], '<'));
        $this->assertTrue(version_compare('10.0.0', $range['max'], '>'));
    }
}
