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
use Mollie\Repository\MolCustomerRepository;
use Mollie\Repository\OrderRepositoryInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Shared\Core\Shared\Repository\CurrencyRepositoryInterface;
use Mollie\Subscription\Provider\SubscriptionDescriptionProvider;
use Mollie\Subscription\Provider\SubscriptionIntervalProvider;
use Mollie\Subscription\Provider\SubscriptionOrderAmountProvider;
use Mollie\Subscription\Provider\SubscriptionStartDateProvider;
use Mollie\Subscription\Repository\CombinationRepository;
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
    /** @var OrderRepositoryInterface */
    public $orderRepository;
    /** @var CurrencyRepositoryInterface */
    public $currencyRepository;
    /** @var \Cart */
    public $cart;
    /** @var PrestaLoggerInterface */
    public $logger;
    /** @var MolCustomerRepository */
    public $molCustomerRepository;
    /** @var SubscriptionIntervalProvider */
    public $subscriptionIntervalProvider;
    /** @var SubscriptionDescriptionProvider */
    public $subscriptionDescriptionProvider;
    /** @var PaymentMethodRepositoryInterface */
    public $paymentMethodRepository;
    /** @var SubscriptionOrderAmountProvider */
    public $subscriptionOrderAmountProvider;
    /** @var SubscriptionStartDateProvider */
    public $subscriptionStartDateProvider;
    /** @var CombinationRepository */
    public $combinationRepository;
    /** @var TimeUtility */
    public $timeUtility;

    protected function setUp(): void
    {
        $this->module = $this->mock(\Mollie::class);
        $this->configuration = $this->mock(ConfigurationAdapter::class);
        $this->context = $this->mock(Context::class);
        $this->customer = $this->mock(\Customer::class);
        $this->moduleFactory = $this->mock(ModuleFactory::class);
        $this->orderRepository = $this->mock(OrderRepositoryInterface::class);
        $this->currencyRepository = $this->mock(CurrencyRepositoryInterface::class);
        $this->cart = $this->mock(\Cart::class);
        $this->logger = $this->mock(PrestaLoggerInterface::class);
        $this->molCustomerRepository = $this->mock(MolCustomerRepository::class);
        $this->subscriptionIntervalProvider = $this->mock(SubscriptionIntervalProvider::class);
        $this->subscriptionDescriptionProvider = $this->mock(SubscriptionDescriptionProvider::class);
        $this->paymentMethodRepository = $this->mock(PaymentMethodRepositoryInterface::class);
        $this->subscriptionOrderAmountProvider = $this->mock(SubscriptionOrderAmountProvider::class);
        $this->subscriptionStartDateProvider = $this->mock(SubscriptionStartDateProvider::class);
        $this->combinationRepository = $this->mock(CombinationRepository::class);
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
