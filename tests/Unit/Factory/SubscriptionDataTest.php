<?php

declare(strict_types=1);

namespace Mollie\Subscription\Tests\Unit\Factory;

use Mollie\Repository\MolCustomerRepository;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Subscription\Config\Config;
use Mollie\Subscription\Constants\IntervalConstant;
use Mollie\Subscription\DTO\CreateSubscriptionData as SubscriptionDataDTO;
use Mollie\Subscription\DTO\Object\Amount;
use Mollie\Subscription\DTO\Object\Interval;
use Mollie\Subscription\Factory\CreateSubscriptionDataFactory;
use Mollie\Subscription\Provider\SubscriptionDescriptionProvider;
use Mollie\Subscription\Provider\SubscriptionIntervalProvider;
use Mollie\Subscription\Repository\CombinationRepository;
use Mollie\Subscription\Repository\CurrencyRepository;
use PHPUnit\Framework\TestCase;

class SubscriptionDataTest extends TestCase
{
    private const TEST_ORDER_ID = 1;
    private const TEST_CURRENCY_ISO = 'EUR';
    private const TEST_MANDATE_ID = 'mandate_id_test';

    /**
     * @dataProvider subscriptionDataProvider
     */
    public function testBuildSubscriptionData(string $customerId, float $totalAmount, string $description, SubscriptionDataDTO $expectedResult): void
    {
        $molCustomer = $this->createMock('MolCustomer');
        $molCustomer->customer_id = $customerId;
        $customerRepositoryMock = $this->createMock(MolCustomerRepository::class);
        $customerRepositoryMock->method('findOneBy')->willReturn($molCustomer);

        $subscriptionIntervalProviderMock = $this->createMock(SubscriptionIntervalProvider::class);
        $subscriptionIntervalProviderMock->method('getSubscriptionInterval')->willReturn(new Interval(1, IntervalConstant::DAY));

        $subscriptionDescriptionProviderMock = $this->createMock(SubscriptionDescriptionProvider::class);
        $subscriptionDescriptionProviderMock->method('getSubscriptionDescription')->willReturn($description);

        $currency = $this->createMock('Currency');
        $currency->iso_code = 'EUR';

        $currencyAdapterMock = $this->createMock(CurrencyRepository::class);
        $currencyAdapterMock->method('getById')->willReturn($currency);

        $paymentMethodRepositoryMock = $this->createMock(PaymentMethodRepository::class);
        $paymentMethodRepositoryMock->method('getPaymentBy')->willReturn(
            [
                'mandate_id' => self::TEST_MANDATE_ID,
            ]
        );

        $subscriptionDataFactory = new CreateSubscriptionDataFactory(
            $customerRepositoryMock,
            $subscriptionIntervalProviderMock,
            $subscriptionDescriptionProviderMock,
            $currencyAdapterMock,
            new CombinationRepository(),
            $paymentMethodRepositoryMock
        );

        $customerMock = $this->createMock('Customer');
        $customerMock->email = 'test.gmail.com';

        $order = $this->createMock('Order');
        $order->method('getCustomer')->willReturn($customerMock);
        $order->method('getCartProducts')->willReturn([
            [
                'id_product_attribute' => 1,
            ],
        ]);
        $order->id = self::TEST_ORDER_ID;
        $order->id_cart = 1;
        $order->id_currency = 1;
        $order->total_paid_tax_incl = $totalAmount;

        $subscriptionData = $subscriptionDataFactory->build($order);

        $this->assertEquals($expectedResult, $subscriptionData);
    }

    public function subscriptionDataProvider()
    {
        $subscriptionDto = new SubscriptionDataDTO(
            'testCustomerId',
            new Amount(19.99, 'EUR'),
            new Interval(1, IntervalConstant::DAY),
            Config::DESCRIPTION_PREFIX . '-' . self::TEST_ORDER_ID . '-' . 19.99 . '-' . self::TEST_CURRENCY_ISO
        );
        $subscriptionDto->setMandateId(self::TEST_MANDATE_ID);

        return [
            'first example' => [
                'customer id' => 'testCustomerId',
                'total paid amount' => 19.99,
                'description' => Config::DESCRIPTION_PREFIX . '-' . self::TEST_ORDER_ID . '-' . 19.99 . '-' . self::TEST_CURRENCY_ISO,
                'expected result' => $subscriptionDto,
            ],
        ];
    }
}
