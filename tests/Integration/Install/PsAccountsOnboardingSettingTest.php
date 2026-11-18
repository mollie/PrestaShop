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

use Mollie\Config\Config;
use Mollie\Install\Installer;
use Mollie\Tests\Integration\BaseTestCase;

require_once __DIR__ . '/../../../upgrade/Upgrade-6.4.8.php';

class PsAccountsOnboardingSettingTest extends BaseTestCase
{
    public function testFreshInstallDefaultsTheOnboardingSwitchToOff()
    {
        \Configuration::deleteByName(Config::MOLLIE_PS_ACCOUNTS_ONBOARDING_ENABLED);
        $installer = $this->getService(Installer::class);

        $initConfig = new \ReflectionMethod($installer, 'initConfig');
        $initConfig->setAccessible(true);
        $initConfig->invoke($installer);

        $value = \Configuration::get(Config::MOLLIE_PS_ACCOUNTS_ONBOARDING_ENABLED);

        $this->assertNotFalse($value);
        $this->assertSame(0, (int) $value);
    }

    public function testUpgradeSeedsTheOnboardingSwitchOnForExistingShops()
    {
        \Configuration::deleteByName(Config::MOLLIE_PS_ACCOUNTS_ONBOARDING_ENABLED);

        $result = upgrade_module_6_4_8(\Module::getInstanceByName('mollie'));

        $this->assertTrue($result);
        $this->assertSame(1, (int) \Configuration::getGlobalValue(Config::MOLLIE_PS_ACCOUNTS_ONBOARDING_ENABLED));
    }

    public function testUpgradeKeepsAnExistingSwitchValue()
    {
        \Configuration::updateGlobalValue(Config::MOLLIE_PS_ACCOUNTS_ONBOARDING_ENABLED, 0);

        upgrade_module_6_4_8(\Module::getInstanceByName('mollie'));

        $value = \Configuration::getGlobalValue(Config::MOLLIE_PS_ACCOUNTS_ONBOARDING_ENABLED);

        $this->assertNotFalse($value);
        $this->assertSame(0, (int) $value);
    }
}
