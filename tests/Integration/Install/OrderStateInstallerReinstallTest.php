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
    private const CHARGEBACK_COLOR = '#E74C3C';

    /** @var array<string, string|false> */
    private $savedValues = [];

    /** @var array<int, array<string, mixed>> */
    private $savedChargebackStates = [];

    /** @var int */
    private $lastOrderStateId;

    protected function setUp()
    {
        parent::setUp();

        // BaseTestCase truncates tables after START TRANSACTION, which commits it, so restore by hand.
        foreach (Config::getMollieOrderStatuses() as $key) {
            $this->savedValues[$key] = \Configuration::get($key);
        }

        foreach ($this->getMollieChargebackStates() as $orderState) {
            $this->savedChargebackStates[(int) $orderState->id] = ['deleted' => (bool) $orderState->deleted, 'name' => $orderState->name];
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

        foreach ($this->savedChargebackStates as $orderStateId => $saved) {
            $orderState = new \OrderState($orderStateId);
            $orderState->deleted = $saved['deleted'];
            $orderState->name = $saved['name'];
            $orderState->update();
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

        $this->assertMappedToMollieState(Config::MOLLIE_STATUS_CHARGEBACK, self::CHARGEBACK_COLOR);
        $this->assertSame($orderStatesBefore, $this->getCountFromDatabase(\OrderState::class, []));
    }

    public function testInstallReusesMollieStateWhenMappingIsMissing()
    {
        \Configuration::deleteByName(Config::MOLLIE_STATUS_CHARGEBACK);
        $orderStatesBefore = $this->getCountFromDatabase(\OrderState::class, []);

        $this->installer()->install();

        $this->assertMappedToMollieState(Config::MOLLIE_STATUS_CHARGEBACK, self::CHARGEBACK_COLOR);
        $this->assertSame($orderStatesBefore, $this->getCountFromDatabase(\OrderState::class, []));
    }

    public function testInstallReusesRenamedMollieState()
    {
        foreach ($this->getMollieChargebackStates() as $orderState) {
            foreach (array_keys($orderState->name) as $langId) {
                $orderState->name[$langId] = 'Terugboeking';
            }
            $orderState->update();
        }
        $this->configuration->updateValue(Config::MOLLIE_STATUS_CHARGEBACK, 0);
        $orderStatesBefore = $this->getCountFromDatabase(\OrderState::class, []);

        $this->installer()->install();

        $this->assertMappedToMollieState(Config::MOLLIE_STATUS_CHARGEBACK, self::CHARGEBACK_COLOR);
        $this->assertSame($orderStatesBefore, $this->getCountFromDatabase(\OrderState::class, []));
    }

    public function testInstallRestoresDeletedMollieState()
    {
        foreach ($this->getMollieChargebackStates() as $orderState) {
            $orderState->deleted = true;
            $orderState->update();
        }
        $this->configuration->updateValue(Config::MOLLIE_STATUS_CHARGEBACK, 0);
        $orderStatesBefore = $this->getCountFromDatabase(\OrderState::class, []);

        $this->installer()->install();

        $this->assertMappedToMollieState(Config::MOLLIE_STATUS_CHARGEBACK, self::CHARGEBACK_COLOR);
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

        $this->assertMappedToMollieState(Config::MOLLIE_STATUS_AWAITING, '#4169E1');
    }

    public function testUpgradeTreatsEmptyValueAsMissing()
    {
        $this->configuration->updateValue(Config::MOLLIE_STATUS_PARTIAL_REFUND, '');

        $this->installer()->upgrade();

        $this->assertMappedToMollieState(Config::MOLLIE_STATUS_PARTIAL_REFUND, '#6F8C9F');
    }

    private function assertMappedToMollieState(string $key, string $color)
    {
        $orderState = new \OrderState((int) $this->configuration->getStored($key));

        $this->assertTrue(\Validate::isLoadedObject($orderState));
        $this->assertFalse((bool) $orderState->deleted);
        $this->assertSame('mollie', $orderState->module_name);
        $this->assertSame(strtoupper($color), strtoupper($orderState->color));
    }

    /**
     * @return \OrderState[]
     */
    private function getMollieChargebackStates(): array
    {
        $orderStateIds = \Db::getInstance()->executeS(
            (new \DbQuery())
                ->select('id_order_state')
                ->from('order_state')
                ->where('module_name = "mollie"')
                ->where('UPPER(color) = "' . self::CHARGEBACK_COLOR . '"')
        );

        return array_map(static function (array $row) {
            return new \OrderState((int) $row['id_order_state']);
        }, $orderStateIds ?: []);
    }

    private function installer(): OrderStateInstaller
    {
        return $this->getService(OrderStateInstaller::class);
    }
}
