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
use Mollie\Install\OrderStateInstaller;
use Mollie\Tests\Integration\BaseTestCase;

class OrderStateInstallerReinstallTest extends BaseTestCase
{
    private const TOUCHED_KEYS = [
        Config::MOLLIE_STATUS_CHARGEBACK,
        Config::MOLLIE_STATUS_PARTIAL_REFUND,
        Config::MOLLIE_STATUS_AWAITING,
    ];

    /** @var array<string, string|false> */
    private $savedValues = [];

    /** @var int */
    private $lastOrderStateId;

    protected function setUp()
    {
        parent::setUp();

        // BaseTestCase truncates tables after START TRANSACTION, which commits it, so restore by hand.
        foreach (self::TOUCHED_KEYS as $key) {
            $this->savedValues[$key] = \Configuration::get($key);
        }

        $this->lastOrderStateId = (int) \Db::getInstance()->getValue(
            (new \DbQuery())->select('MAX(id_order_state)')->from('order_state')
        );
    }

    protected function tearDown()
    {
        foreach ($this->savedValues as $key => $value) {
            if ($value === false) {
                \Configuration::deleteByName($key);

                continue;
            }

            $this->configuration->updateValue($key, $value);
        }

        $createdStates = new \PrestaShopCollection(\OrderState::class);
        $createdStates->where('id_order_state', '>', $this->lastOrderStateId);

        foreach ($createdStates as $orderState) {
            $orderState->delete();
        }

        parent::tearDown();
    }

    public function testInstallPutsMollieStateBackOnSkippedStatusWithoutDuplicate()
    {
        $this->configuration->updateValue(Config::MOLLIE_STATUS_CHARGEBACK, 0);
        $orderStatesBefore = $this->getCountFromDatabase(\OrderState::class, []);

        $this->installer()->install();

        $this->assertMappedToMollieState(Config::MOLLIE_STATUS_CHARGEBACK, 'Mollie Chargeback');
        $this->assertSame($orderStatesBefore, $this->getCountFromDatabase(\OrderState::class, []));
    }

    public function testInstallReusesMollieStateWhenMappingIsMissing()
    {
        \Configuration::deleteByName(Config::MOLLIE_STATUS_CHARGEBACK);
        $orderStatesBefore = $this->getCountFromDatabase(\OrderState::class, []);

        $this->installer()->install();

        $this->assertMappedToMollieState(Config::MOLLIE_STATUS_CHARGEBACK, 'Mollie Chargeback');
        $this->assertSame($orderStatesBefore, $this->getCountFromDatabase(\OrderState::class, []));
    }

    public function testInstallKeepsCustomOrderState()
    {
        $customStateId = (int) \Configuration::get('PS_OS_PAYMENT');
        $this->configuration->updateValue(Config::MOLLIE_STATUS_CHARGEBACK, $customStateId);

        $this->installer()->install();

        $this->assertSame($customStateId, (int) $this->configuration->getStored(Config::MOLLIE_STATUS_CHARGEBACK));
    }

    public function testUpgradeKeepsSkippedStatus()
    {
        $this->configuration->updateValue(Config::MOLLIE_STATUS_CHARGEBACK, 0);
        $orderStatesBefore = $this->getCountFromDatabase(\OrderState::class, []);

        $this->installer()->upgrade();

        $this->assertSame('0', (string) $this->configuration->getStored(Config::MOLLIE_STATUS_CHARGEBACK));
        $this->assertSame($orderStatesBefore, $this->getCountFromDatabase(\OrderState::class, []));
    }

    public function testUpgradeReplacesZeroOnStatusOrdersAreCreatedIn()
    {
        $this->configuration->updateValue(Config::MOLLIE_STATUS_AWAITING, 0);

        $this->installer()->upgrade();

        $this->assertMappedToMollieState(Config::MOLLIE_STATUS_AWAITING, 'Awaiting Mollie payment');
    }

    public function testUpgradeTreatsEmptyValueAsMissing()
    {
        $this->configuration->updateValue(Config::MOLLIE_STATUS_PARTIAL_REFUND, '');

        $this->installer()->upgrade();

        $this->assertMappedToMollieState(Config::MOLLIE_STATUS_PARTIAL_REFUND, 'Partially refunded by Mollie');
    }

    private function assertMappedToMollieState(string $key, string $name)
    {
        $orderState = new \OrderState((int) $this->configuration->getStored($key), (int) \Configuration::get('PS_LANG_DEFAULT'));

        $this->assertTrue(\Validate::isLoadedObject($orderState));
        $this->assertFalse((bool) $orderState->deleted);
        $this->assertSame('mollie', $orderState->module_name);
        $this->assertSame($name, $orderState->name);
    }

    private function installer(): OrderStateInstaller
    {
        return $this->getService(OrderStateInstaller::class);
    }
}
