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

namespace Mollie\Tests\Integration;

use Module;
use Mollie;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Tests\Integration\Subscription\Tool\ContextBuilder;
use PHPUnit\Framework\TestCase;
use Shop;

class BaseTestCase extends TestCase
{
    protected $backupGlobals = false;
    /** @var ContextBuilder */
    public $contextBuilder;

    /** @var ConfigurationAdapter */
    protected $configuration;

    protected function setUp()
    {
        parent::setUp();

        \Db::getInstance()->execute('START TRANSACTION;');

        self::clearCache();

        // Some tests might have cleared the configuration
        \Configuration::loadConfiguration();

        \Cache::clear();

        $this->contextBuilder = new ContextBuilder();
        $this->contextBuilder->setDefaults();
        $this->configuration = new ConfigurationAdapter();
    }

    protected function tearDown()
    {
        \Db::getInstance()->execute('ROLLBACK;');

        parent::tearDown();
    }

    public function assertDatabaseHas($entityClass, array $keyValueCriteria)
    {
        $this->assertNotEmpty($this->getValueFromDatabase($entityClass, $keyValueCriteria), 'Failed asserting that database has record.');
    }

    public function assertDatabaseHasNot($entityClass, array $keyValueCriteria)
    {
        $this->assertEmpty($this->getValueFromDatabase($entityClass, $keyValueCriteria), 'Failed asserting that database does not have record.');
    }

    public function assertDatabaseCount($entityClass, array $keyValueCriteria, $count)
    {
        $this->assertCount($count, $this->getCountFromDatabase($entityClass, $keyValueCriteria), 'Failed asserting that database count is equal to given count');
    }

    public function getValueFromDatabase($entityClass, array $keyValueCriteria)
    {
        $psCollection = new \PrestaShopCollection($entityClass);

        foreach ($keyValueCriteria as $field => $value) {
            $psCollection = $psCollection->where($field, '=', $value);
        }

        if (!$psCollection->offsetExists(0)) {
            return false;
        }

        return $psCollection->getFirst();
    }

    public function getCountFromDatabase($entityClass, array $keyValueCriteria)
    {
        $psCollection = new \PrestaShopCollection($entityClass);

        foreach ($keyValueCriteria as $field => $value) {
            $psCollection = $psCollection->where($field, '=', $value);
        }

        return $psCollection->count();
    }

    public function getContextShopId()
    {
        //NOTE returns null value if without multishop
        return Shop::getContextShopID(true) ? (int) Shop::getContextShopID(true) : null;
    }

    protected function getService($serviceName)
    {
        /** @var Mollie $mollie */
        $mollie = Module::getInstanceByName('mollie');

        return $mollie->getService($serviceName);
    }

    public function getContextBuilder()
    {
        return $this->contextBuilder;
    }

    private static function clearCache()
    {
        if (method_exists(\Cache::class, 'clear')) {
            \Cache::clear();
        }

        if (method_exists(\Cache::class, 'clean')) {
            \Cache::clean('*');
        }

        if (method_exists(\Cart::class, 'resetStaticCache')) {
            \Cart::resetStaticCache();
        }

        if (method_exists(\TaxManagerFactory::class, 'resetStaticCache')) {
            \TaxManagerFactory::resetStaticCache();
        }

        if (method_exists(\Address::class, 'resetStaticCache')) {
            \Address::resetStaticCache();
        }

        if (method_exists(\Carrier::class, 'resetStaticCache')) {
            \Carrier::resetStaticCache();
        }

        if (method_exists(\CartRule::class, 'resetStaticCache')) {
            \CartRule::resetStaticCache();
        }

        if (method_exists(\Currency::class, 'resetStaticCache')) {
            \Currency::resetStaticCache();
        }

        if (method_exists(\GroupReduction::class, 'resetStaticCache')) {
            \GroupReduction::resetStaticCache();
        }

        if (method_exists(\Pack::class, 'resetStaticCache')) {
            \Pack::resetStaticCache();
        }

        if (method_exists(\Product::class, 'resetStaticCache')) {
            \Product::resetStaticCache();
        }

        if (method_exists(\Combination::class, 'resetStaticCache')) {
            \Combination::resetStaticCache();
        }

        if (method_exists(\Tools::class, 'resetStaticCache')) {
            \Tools::resetStaticCache();
        }

        if (method_exists(\Tab::class, 'resetStaticCache')) {
            \Tab::resetStaticCache();
        }
    }
}
