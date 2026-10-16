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

namespace Mollie\Tests\Unit\Service\ExpressComponent;

use Mollie\Api\MollieApiClient;
use Mollie\Config\Config;
use Mollie\Factory\ModuleFactory;
use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\ExpressComponent\ExpressComponentAvailabilityService;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidationInterface;
use Mollie\Subscription\Validator\SubscriptionOrderValidator;
use Mollie\Tests\Unit\BaseTestCase;

class ExpressComponentAvailabilityServiceTest extends BaseTestCase
{
    /** @var PaymentMethodRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $paymentMethodRepository;
    /** @var SubscriptionOrderValidator|\PHPUnit\Framework\MockObject\MockObject */
    private $subscriptionOrderValidator;
    /** @var ModuleFactory|\PHPUnit\Framework\MockObject\MockObject */
    private $moduleFactory;
    /** @var PaymentMethodRestrictionValidationInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $restrictionValidation;
    /** @var array<string, mixed> */
    private $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentMethodRepository = $this->mock(PaymentMethodRepositoryInterface::class);
        $this->subscriptionOrderValidator = $this->mock(SubscriptionOrderValidator::class);
        $this->moduleFactory = $this->mock(ModuleFactory::class);
        $this->restrictionValidation = $this->mock(PaymentMethodRestrictionValidationInterface::class);
        $this->restrictionValidation->method('isPaymentMethodValid')->willReturn(true);

        $module = $this->mock(\Mollie::class);
        $module->method('getApiClient')->willReturn($this->mock(MollieApiClient::class));
        $this->moduleFactory->method('getModule')->willReturn($module);

        $this->config = [
            Config::MOLLIE_EXPRESS_COMPONENT_ENABLED => '1',
            Config::MOLLIE_ENVIRONMENT => (string) Config::ENVIRONMENT_LIVE,
            Config::MOLLIE_API_KEY => 'live_abc',
            Config::MOLLIE_EXPRESS_COMPONENT_APPLEPAY => '1',
            Config::MOLLIE_EXPRESS_COMPONENT_GOOGLEPAY => '1',
            Config::MOLLIE_EXPRESS_COMPONENT_PAYPAL => '0',
        ];
        $this->configuration->method('get')->willReturnCallback(function ($key) {
            return $this->config[$key] ?? null;
        });
        $this->context->method('getShopId')->willReturn(1);

        $this->cart->id = 7;
        $this->cart->id_customer = 3;
        $this->cart->id_address_delivery = 4;
        $this->cart->id_address_invoice = 4;
        $this->cart->id_carrier = 2;
        $this->cart->method('getOrderTotal')->willReturn(59.9);
        $this->cart->method('getDeliveryOption')->willReturn([4 => '2,']);
    }

    private function service(): ExpressComponentAvailabilityService
    {
        return new ExpressComponentAvailabilityService(
            $this->configuration,
            $this->context,
            $this->paymentMethodRepository,
            $this->restrictionValidation,
            $this->subscriptionOrderValidator,
            $this->moduleFactory,
            $this->mock(PrestaLoggerInterface::class)
        );
    }

    public function testItIsEnabledInLiveModeWithALiveKey(): void
    {
        $this->assertTrue($this->service()->isEnabled());
    }

    /**
     * @dataProvider disabledConfigProvider
     */
    public function testItIsDisabledWhenTheShopCannotUseTheBeta(array $overrides, string $reason): void
    {
        $this->config = array_merge($this->config, $overrides);

        $service = $this->service();

        $this->assertFalse($service->isEnabled());
        $this->assertSame($reason, $service->getUnavailableReason());
    }

    public function disabledConfigProvider(): array
    {
        return [
            'switched off' => [[Config::MOLLIE_EXPRESS_COMPONENT_ENABLED => '0'], 'disabled in settings'],
            'test environment' => [[Config::MOLLIE_ENVIRONMENT => (string) Config::ENVIRONMENT_TEST], 'environment is not live'],
            'test key in live environment' => [[Config::MOLLIE_API_KEY => 'test_abc'], 'live environment uses a test API key'],
        ];
    }

    public function testItIsDisabledWithoutAnApiClient(): void
    {
        $moduleFactory = $this->mock(ModuleFactory::class);
        $moduleFactory->method('getModule')->willReturn(null);

        $service = new ExpressComponentAvailabilityService(
            $this->configuration,
            $this->context,
            $this->paymentMethodRepository,
            $this->restrictionValidation,
            $this->subscriptionOrderValidator,
            $moduleFactory,
            $this->mock(PrestaLoggerInterface::class)
        );

        $this->assertFalse($service->isEnabled());
        $this->assertSame('API client is not available', $service->getUnavailableReason());
    }

    public function testACompleteCartIsAvailable(): void
    {
        $this->subscriptionOrderValidator->method('validate')->willReturn(false);
        $this->offerEveryMethod();

        $this->assertTrue($this->service()->isAvailable($this->cart));
    }

    public function testACartWithEveryButtonSwitchedOffIsNotAvailable(): void
    {
        $this->subscriptionOrderValidator->method('validate')->willReturn(false);
        $this->offerEveryMethod();
        $this->config[Config::MOLLIE_EXPRESS_COMPONENT_APPLEPAY] = '0';
        $this->config[Config::MOLLIE_EXPRESS_COMPONENT_GOOGLEPAY] = '0';

        $service = $this->service();

        $this->assertFalse($service->isAvailable($this->cart));
        $this->assertSame('no express button is offered for this cart', $service->getUnavailableReason());
    }

    private function offerEveryMethod(): void
    {
        $enabledMethod = new \MolPaymentMethod();
        $enabledMethod->enabled = true;

        $this->paymentMethodRepository->method('getPaymentMethodIdByMethodId')->willReturn(11);
        $this->paymentMethodRepository->method('findOneBy')->willReturn($enabledMethod);
    }

    public function testACartWithoutAStoredCarrierUsesTheShopsDefault(): void
    {
        $this->cart->id_carrier = 0;
        $this->subscriptionOrderValidator->method('validate')->willReturn(false);
        $this->offerEveryMethod();

        $this->assertTrue($this->service()->isAvailable($this->cart));
    }

    public function testAVirtualCartWithoutACarrierIsAvailable(): void
    {
        $cart = $this->mock(\Cart::class);
        $cart->id = 9;
        $cart->id_customer = 3;
        $cart->id_address_delivery = 4;
        $cart->id_address_invoice = 4;
        $cart->id_carrier = 0;
        $cart->method('isVirtualCart')->willReturn(true);
        $cart->method('getDeliveryOption')->willReturn([]);
        $cart->method('getOrderTotal')->willReturn(10.8);
        $this->subscriptionOrderValidator->method('validate')->willReturn(false);
        $this->offerEveryMethod();

        $this->assertTrue($this->service()->isAvailable($cart));
    }

    public function testACartNoCarrierCanShipIsNotAvailable(): void
    {
        $cart = $this->mock(\Cart::class);
        $cart->id = 8;
        $cart->id_customer = 3;
        $cart->id_address_delivery = 9;
        $cart->id_address_invoice = 9;
        $cart->id_carrier = 2;
        $cart->method('getDeliveryOption')->willReturn([]);
        $cart->method('getOrderTotal')->willReturn(59.9);

        $service = $this->service();

        $this->assertFalse($service->isAvailable($cart));
        $this->assertSame('no carrier serves the delivery address', $service->getUnavailableReason());
    }

    public function testACartBelowTheMinimumPurchaseTotalIsNotAvailable(): void
    {
        $this->config['PS_PURCHASE_MINIMUM'] = '500';
        $this->cart->id_currency = 1;

        $service = $this->service();

        $this->assertFalse($service->isAvailable($this->cart));
        $this->assertSame('cart is below the minimum purchase total', $service->getUnavailableReason());
    }

    public function testASubscriptionCartIsNotAvailable(): void
    {
        $this->subscriptionOrderValidator->method('validate')->willReturn(true);

        $service = $this->service();

        $this->assertFalse($service->isAvailable($this->cart));
        $this->assertSame('cart contains a subscription product', $service->getUnavailableReason());
    }

    public function testButtonVisibilityCombinesTheSettingWithTheMethodRow(): void
    {
        $enabledMethod = new \MolPaymentMethod();
        $enabledMethod->enabled = 1;

        $this->paymentMethodRepository->method('getPaymentMethodIdByMethodId')->willReturnMap([
            ['applepay', Config::ENVIRONMENT_LIVE, 1, 11],
            ['creditcard', Config::ENVIRONMENT_LIVE, 1, 0],
            ['paypal', Config::ENVIRONMENT_LIVE, 1, 13],
        ]);
        $this->paymentMethodRepository->method('findOneBy')->willReturn($enabledMethod);

        $this->assertSame([
            'applepay' => 'automatic',
            'googlepay' => 'hidden',
            'paypal' => 'hidden',
        ], $this->service()->getButtonVisibility());
    }

    public function testADisabledMethodRowHidesItsButton(): void
    {
        $disabledMethod = new \MolPaymentMethod();
        $disabledMethod->enabled = 0;

        $this->paymentMethodRepository->method('getPaymentMethodIdByMethodId')->willReturn(11);
        $this->paymentMethodRepository->method('findOneBy')->willReturn($disabledMethod);

        $visibility = $this->service()->getButtonVisibility();

        $this->assertSame('hidden', $visibility['applepay']);
        $this->assertSame('hidden', $visibility['googlepay']);
    }

    public function testAMethodTheShopRestrictsHidesItsButton(): void
    {
        $enabledMethod = new \MolPaymentMethod();
        $enabledMethod->enabled = 1;

        $this->paymentMethodRepository->method('getPaymentMethodIdByMethodId')->willReturn(11);
        $this->paymentMethodRepository->method('findOneBy')->willReturn($enabledMethod);

        $restrictionValidation = $this->mock(PaymentMethodRestrictionValidationInterface::class);
        $restrictionValidation->method('isPaymentMethodValid')->willReturn(false);

        $service = new ExpressComponentAvailabilityService(
            $this->configuration,
            $this->context,
            $this->paymentMethodRepository,
            $restrictionValidation,
            $this->subscriptionOrderValidator,
            $this->moduleFactory,
            $this->mock(PrestaLoggerInterface::class)
        );

        $this->assertSame([
            'applepay' => 'hidden',
            'googlepay' => 'hidden',
            'paypal' => 'hidden',
        ], $service->getButtonVisibility());
    }
}
