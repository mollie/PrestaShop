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

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\Context;
use Mollie\Factory\ModuleFactory;
use Mollie\Repository\OrderRepositoryInterface;
use Mollie\Shared\Core\Shared\Repository\CurrencyRepositoryInterface;
use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
    protected $backupGlobals = false;

    /** @var \Mollie */
    public $module;
    /** @var ConfigurationAdapter */
    public $configuration;
    /** @var Context */
    public $context;
    /** @var \Customer */
    public $customer;
    /** @var ModuleFactory */
    public $moduleFactory;
    /** @var OrderRepositoryInterface */
    public $orderRepository;
    /** @var CurrencyRepositoryInterface */
    public $currencyRepository;

    protected function setUp(): void
    {
        $this->module = $this->mock(\Mollie::class);
        $this->configuration = $this->mock(ConfigurationAdapter::class);
        $this->context = $this->mock(Context::class);
        $this->customer = $this->mock(\Customer::class);
        $this->moduleFactory = $this->mock(ModuleFactory::class);
        $this->orderRepository = $this->mock(OrderRepositoryInterface::class);
        $this->currencyRepository = $this->mock(CurrencyRepositoryInterface::class);

        parent::setUp();
    }

    public function mock(string $className)
    {
        return $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
