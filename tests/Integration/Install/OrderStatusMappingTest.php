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
use Mollie\Install\OrderStatusMapping;
use Mollie\Tests\Integration\BaseTestCase;

class OrderStatusMappingTest extends BaseTestCase
{
    private const TOUCHED_KEYS = [
        Config::MOLLIE_STATUS_REFUNDED,
        Config::MOLLIE_STATUS_CANCELED,
        Config::MOLLIE_STATUS_EXPIRED,
        Config::MOLLIE_STATUS_PAID,
        Config::MOLLIE_STATUS_CHARGEBACK,
        Config::MOLLIE_STATUS_AWAITING,
        Config::MOLLIE_STATUS_PARTIALLY_SHIPPED,
        Config::MOLLIE_STATUS_COMPLETED,
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

    public function testItIsNotSetWhenOrderStateIsDeleted()
    {
        $orderState = new \OrderState();
        $orderState->name = [(int) \Configuration::get('PS_LANG_DEFAULT') => 'Deleted custom state'];
        $orderState->color = '#000000';
        $orderState->add();
        $orderState->deleted = true;
        $orderState->update();
        $this->configuration->updateValue(Config::MOLLIE_STATUS_REFUNDED, (int) $orderState->id);

        $this->assertFalse($this->mapping()->isSet(Config::MOLLIE_STATUS_REFUNDED));
    }

    public function testEmptyValueIsNotASkip()
    {
        $this->configuration->updateValue(Config::MOLLIE_STATUS_COMPLETED, '');

        $this->assertFalse($this->mapping()->isSkipped(Config::MOLLIE_STATUS_COMPLETED));
        $this->assertFalse($this->mapping()->isSet(Config::MOLLIE_STATUS_COMPLETED));
    }

    public function testItIsNotSetWhenKeyIsMissing()
    {
        \Configuration::deleteByName(Config::MOLLIE_STATUS_REFUNDED);

        $this->assertFalse($this->mapping()->isSet(Config::MOLLIE_STATUS_REFUNDED));
    }

    public function testItKeepsSkipOnStatusThatAllowsSkip()
    {
        $this->configuration->updateValue(Config::MOLLIE_STATUS_REFUNDED, 0);

        $this->assertTrue($this->mapping()->isSet(Config::MOLLIE_STATUS_REFUNDED));
        $this->assertTrue($this->mapping()->isSkipped(Config::MOLLIE_STATUS_REFUNDED));
    }

    public function testItDoesNotKeepSkipOnStatusOrdersAreCreatedIn()
    {
        $this->configuration->updateValue(Config::MOLLIE_STATUS_PAID, 0);

        $this->assertFalse($this->mapping()->isSet(Config::MOLLIE_STATUS_PAID));
        $this->assertFalse($this->mapping()->isSkipped(Config::MOLLIE_STATUS_PAID));
    }

    public function testItKeepsExistingOrderState()
    {
        $this->configuration->updateValue(Config::MOLLIE_STATUS_REFUNDED, (int) \Configuration::get('PS_OS_PAYMENT'));

        $this->assertTrue($this->mapping()->isSet(Config::MOLLIE_STATUS_REFUNDED));
        $this->assertFalse($this->mapping()->isSkipped(Config::MOLLIE_STATUS_REFUNDED));
    }

    public function testItDoesNotKeepMissingOrderState()
    {
        $this->configuration->updateValue(Config::MOLLIE_STATUS_REFUNDED, 999999);

        $this->assertFalse($this->mapping()->isSet(Config::MOLLIE_STATUS_REFUNDED));
    }

    public function testInstallSetsDefaultOnlyWhenNotSet()
    {
        $this->configuration->updateValue(Config::MOLLIE_STATUS_REFUNDED, 0);
        $this->configuration->updateValue(Config::MOLLIE_STATUS_CANCELED, (int) \Configuration::get('PS_OS_PAYMENT'));
        \Configuration::deleteByName(Config::MOLLIE_STATUS_EXPIRED);
        $this->configuration->updateValue(Config::MOLLIE_STATUS_PAID, 0);

        $this->mapping()->install();

        $this->assertTrue($this->mapping()->isSkipped(Config::MOLLIE_STATUS_REFUNDED));
        $this->assertSame(\Configuration::get('PS_OS_PAYMENT'), \Configuration::get(Config::MOLLIE_STATUS_CANCELED));
        $this->assertSame(\Configuration::get('PS_OS_CANCELED'), \Configuration::get(Config::MOLLIE_STATUS_EXPIRED));
        $this->assertSame(\Configuration::get('PS_OS_PAYMENT'), \Configuration::get(Config::MOLLIE_STATUS_PAID));
    }

    public function testOrderStateInstallerKeepsSkippedStatus()
    {
        $this->configuration->updateValue(Config::MOLLIE_STATUS_CHARGEBACK, 0);
        $orderStatesBefore = $this->getCountFromDatabase(\OrderState::class, []);

        $this->getService(OrderStateInstaller::class)->install();

        $this->assertTrue($this->mapping()->isSkipped(Config::MOLLIE_STATUS_CHARGEBACK));
        $this->assertSame($orderStatesBefore, $this->getCountFromDatabase(\OrderState::class, []));
    }

    public function testOrderStateInstallerReplacesZeroOnStatusOrdersAreCreatedIn()
    {
        $this->configuration->updateValue(Config::MOLLIE_STATUS_AWAITING, 0);

        $this->getService(OrderStateInstaller::class)->install();

        $this->assertTrue(\Validate::isLoadedObject(new \OrderState((int) \Configuration::get(Config::MOLLIE_STATUS_AWAITING))));
    }

    public function testOrderStateInstallerReplacesZeroOnModuleOwnState()
    {
        $this->configuration->updateValue(Config::MOLLIE_STATUS_PARTIALLY_SHIPPED, 0);

        $this->getService(OrderStateInstaller::class)->install();

        $this->assertTrue(\Validate::isLoadedObject(new \OrderState((int) \Configuration::get(Config::MOLLIE_STATUS_PARTIALLY_SHIPPED))));
    }

    private function mapping(): OrderStatusMapping
    {
        return $this->getService(OrderStatusMapping::class);
    }
}
