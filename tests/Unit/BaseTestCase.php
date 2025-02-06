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
use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Shared\Core\Shared\Repository\CurrencyRepositoryInterface;
use Mollie\Utility\TimeUtility;
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
    /** @var CurrencyRepositoryInterface */
    public $currencyRepository;
    /** @var \Cart */
    public $cart;
    /** @var PrestaLoggerInterface */
    public $logger;
    /** @var TimeUtility */
    public $timeUtility;

    protected function setUp(): void
    {
        $this->module = $this->mock(\Mollie::class);
        $this->configuration = $this->mock(ConfigurationAdapter::class);
        $this->context = $this->mock(Context::class);
        $this->customer = $this->mock(\Customer::class);
        $this->moduleFactory = $this->mock(ModuleFactory::class);
        $this->currencyRepository = $this->mock(CurrencyRepositoryInterface::class);
        $this->cart = $this->mock(\Cart::class);
        $this->logger = $this->mock(PrestaLoggerInterface::class);
        $this->timeUtility = $this->mock(TimeUtility::class);

        parent::setUp();
    }

    public function mock(string $className)
    {
        return $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
