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

use Mollie\Action\CreateOrderPaymentFeeAction;
use Mollie\Action\UpdateOrderTotalsAction;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\Context;
use Mollie\Factory\ModuleFactory;
use Mollie\Logger\LoggerInterface;
use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Provider\PaymentFeeProviderInterface;
use Mollie\Repository\CartRepositoryInterface;
use Mollie\Repository\OrderRepositoryInterface;
use Mollie\Service\PaymentMethod\PaymentMethodSortProvider;
use Mollie\Service\PaymentMethodService;
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
    /** @var TimeUtility */
    public $timeUtility;
    /** @var LoggerInterface */
    public $logger;
    /** @var PrestaLoggerInterface */
    public $prestaShopLogger;
    /** @var CartRepositoryInterface */
    public $cartRepository;
    /** @var OrderRepositoryInterface */
    public $orderRepository;
    /** @var UpdateOrderTotalsAction */
    public $updateOrderTotalsAction;
    /** @var CreateOrderPaymentFeeAction */
    public $createOrderPaymentFeeAction;
    /** @var PaymentFeeProviderInterface */
    public $paymentFeeProvider;
    /** @var PaymentMethodService */
    public $paymentMethodService;

    protected function setUp(): void
    {
        $this->module = $this->mock(\Mollie::class);
        $this->configuration = $this->mock(ConfigurationAdapter::class);
        $this->context = $this->mock(Context::class);
        $this->customer = $this->mock(\Customer::class);
        $this->moduleFactory = $this->mock(ModuleFactory::class);
        $this->currencyRepository = $this->mock(CurrencyRepositoryInterface::class);
        $this->cart = $this->mock(\Cart::class);
        $this->prestaShopLogger = $this->mock(PrestaLoggerInterface::class);
        $this->timeUtility = $this->mock(TimeUtility::class);
        $this->logger = $this->mock(LoggerInterface::class);
        $this->paymentMethodService = $this->mock(PaymentMethodService::class);
        $this->paymentFeeProvider = $this->mock(PaymentFeeProviderInterface::class);
        $this->createOrderPaymentFeeAction = $this->mock(CreateOrderPaymentFeeAction::class);
        $this->updateOrderTotalsAction = $this->mock(UpdateOrderTotalsAction::class);
        $this->orderRepository = $this->mock(OrderRepositoryInterface::class);
        $this->cartRepository = $this->mock(CartRepositoryInterface::class);

        parent::setUp();
    }

    public function mock(string $className)
    {
        return $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
