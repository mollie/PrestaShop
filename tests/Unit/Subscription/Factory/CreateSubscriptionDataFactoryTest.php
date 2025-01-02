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

declare(strict_types=1);

namespace Mollie\Tests\Unit\Subscription\Factory;

use Mollie\Repository\MolCustomerRepository;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Subscription\Constants\IntervalConstant;
use Mollie\Subscription\DTO\CreateSubscriptionData as SubscriptionDataDTO;
use Mollie\Subscription\DTO\Object\Amount;
use Mollie\Subscription\DTO\Object\Interval;
use Mollie\Subscription\Factory\CreateSubscriptionDataFactory;
use Mollie\Subscription\Provider\SubscriptionDescriptionProvider;
use Mollie\Subscription\Provider\SubscriptionIntervalProvider;
use Mollie\Subscription\Provider\SubscriptionOrderAmountProvider;
use Mollie\Tests\Unit\BaseTestCase;
use Mollie\Utility\SecureKeyUtility;

class CreateSubscriptionDataFactoryTest extends BaseTestCase
{
    private const TEST_ORDER_ID = 1;
    private const TEST_ORDER_REFERENCE = 111;
    private const TEST_CUSTOMER_ID = 222;
    private const TEST_CART_ID = 333;
    private const TEST_CURRENCY_ISO = 'EUR';
    private const TEST_MANDATE_ID = 'mandate_id_test';

    /**
     * @dataProvider subscriptionDataProvider
     */
    public function testBuildSubscriptionData(string $customerId, float $totalAmount, string $description, SubscriptionDataDTO $expectedResult): void
    {
        // TODO replace data provider with multiple methods, which tests various exception cases
        // TODO needs to be refactored as Integration test

//        /** @var \MolCustomer $molCustomer */
//        $molCustomer = $this->createMock(\MolCustomer::class);
//        $molCustomer->customer_id = $customerId;
//
//        $customerRepository = $this->createMock(MolCustomerRepository::class);
//        $customerRepository->method('findOneBy')->willReturn($molCustomer);
//
//        $interval = new Interval(1, 'day');
//
//        $subscriptionIntervalProvider = $this->createMock(SubscriptionIntervalProvider::class);
//        $subscriptionIntervalProvider->method('getSubscriptionInterval')->willReturn($interval);
//
//        $subscriptionDescriptionProviderMock = $this->createMock(SubscriptionDescriptionProvider::class);
//        $subscriptionDescriptionProviderMock->method('getSubscriptionDescription')->willReturn($description);
//
//        $this->configuration->method('get')->willReturn(1);
//
//        $subscriptionOrderAmountProvider = $this->createMock(SubscriptionOrderAmountProvider::class);
//        $subscriptionOrderAmountProvider->method('get')->willReturn(new Amount($totalAmount, 'EUR'));
//
//        $paymentMethodRepositoryMock = $this->createMock(PaymentMethodRepository::class);
//        $paymentMethodRepositoryMock->method('getPaymentBy')->willReturn(
//            [
//                'mandate_id' => self::TEST_MANDATE_ID,
//            ]
//        );
//
//        $this->context->method('getModuleLink')->willReturn('example-link');
//
//        $this->module->name = 'mollie';
//
//        $subscriptionDataFactory = new CreateSubscriptionDataFactory(
//            $this->molCustomerRepository,
//            $this->subscriptionIntervalProvider,
//            $this->subscriptionDescriptionProvider,
//            $this->paymentMethodRepository,
//            $this->module,
//            $this->context,
//            $this->configuration,
//            $this->subscriptionOrderAmountProvider,
//            $this->subscriptionStartDateProvider,
//            $this->combinationRepository
//        );
//
//        $this->customer->email = 'demo@prestashop.com';
//
//        $order = $this->createMock('Order');
//        $order->method('getCustomer')->willReturn($this->customer);
//
//        $order->id = self::TEST_ORDER_ID;
//        $order->reference = self::TEST_ORDER_REFERENCE;
//        $order->id_cart = self::TEST_CART_ID;
//        $order->id_customer = self::TEST_CUSTOMER_ID;
//        $order->id_currency = 1;
//        $order->total_paid_tax_incl = $totalAmount;
//
//        $subscriptionProduct = [
//            'id_product_attribute' => 1,
//            'total_price_tax_incl' => 29.99,
//        ];
//
//        $subscriptionData = $subscriptionDataFactory->build($order, $subscriptionProduct);
//
//        $this->assertEquals($expectedResult, $subscriptionData);
    }
}
