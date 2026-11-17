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

use Mollie\Adapter\ToolsAdapter;
use Mollie\Api\MollieApiClient;
use Mollie\Config\Config;
use Mollie\Factory\ModuleFactory;
use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Repository\ModuleRestrictionRepositoryInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\ExpressComponent\ExpressComponentAvailabilityService;
use Mollie\Service\MinimumPurchaseService;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidation\ApplePayPaymentMethodRestrictionValidator;
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
    /** @var ModuleRestrictionRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $moduleRestrictionRepository;
    /** @var array<string, bool> repository method => answer */
    private $restrictions;
    /** @var array<string, array[]> repository method => arguments of each call */
    private $restrictionCalls = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentMethodRepository = $this->mock(PaymentMethodRepositoryInterface::class);
        $this->subscriptionOrderValidator = $this->mock(SubscriptionOrderValidator::class);
        $this->moduleFactory = $this->mock(ModuleFactory::class);
        $this->restrictionValidation = $this->mock(PaymentMethodRestrictionValidationInterface::class);
        $this->restrictionValidation->method('isPaymentMethodValid')->willReturn(true);

        $module = $this->mock(\Mollie::class);
        $module->id = 42;
        $module->method('getApiClient')->willReturn($this->mock(MollieApiClient::class));
        $this->moduleFactory->method('getModule')->willReturn($module);

        $this->restrictions = [
            'isCurrencyAllowed' => true,
            'isCountryAllowed' => true,
            'isCarrierReferenceAllowed' => true,
            'isAnyGroupAllowed' => true,
        ];
        $this->moduleRestrictionRepository = $this->mock(ModuleRestrictionRepositoryInterface::class);

        foreach (array_keys($this->restrictions) as $restriction) {
            $this->moduleRestrictionRepository->method($restriction)->willReturnCallback(function () use ($restriction) {
                $this->restrictionCalls[$restriction][] = func_get_args();

                return $this->restrictions[$restriction];
            });
        }

        $this->config = [
            Config::MOLLIE_EXPRESS_COMPONENT_ENABLED => '1',
            Config::MOLLIE_ENVIRONMENT => (string) Config::ENVIRONMENT_LIVE,
            Config::MOLLIE_API_KEY => 'live_abc',
            Config::MOLLIE_EXPRESS_COMPONENT_APPLEPAY => '1',
            Config::MOLLIE_EXPRESS_COMPONENT_GOOGLEPAY => '1',
            Config::MOLLIE_EXPRESS_COMPONENT_PAYPAL => '0',
            'PS_GROUP_FEATURE_ACTIVE' => '1',
            'PS_GUEST_GROUP' => '2',
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
        $this->cart->id_currency = 1;
        $this->cart->method('getOrderTotal')->willReturn(59.9);
        $this->cart->method('getDeliveryOption')->willReturn([4 => '2,']);
    }

    private function minimumPurchaseService(): MinimumPurchaseService
    {
        return new MinimumPurchaseService($this->configuration, $this->mock(ToolsAdapter::class), $this->context);
    }

    /**
     * @return ExpressComponentAvailabilityService|\PHPUnit\Framework\MockObject\MockObject
     */
    private function service()
    {
        $service = $this->getMockBuilder(ExpressComponentAvailabilityService::class)
            ->setConstructorArgs([
                $this->configuration,
                $this->context,
                $this->paymentMethodRepository,
                $this->restrictionValidation,
                $this->subscriptionOrderValidator,
                $this->moduleFactory,
                $this->mock(PrestaLoggerInterface::class),
                $this->minimumPurchaseService(),
                $this->moduleRestrictionRepository,
            ])
            ->setMethods(['getCustomerGroupIds', 'getAddressCountryId', 'getCarrierReference'])
            ->getMock();

        $service->method('getCustomerGroupIds')->willReturn([3, 5]);
        $service->method('getAddressCountryId')->willReturn(17);
        $service->method('getCarrierReference')->willReturnCallback(static function (int $carrierId) {
            return $carrierId + 100;
        });

        return $service;
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
            $this->mock(PrestaLoggerInterface::class),
            $this->minimumPurchaseService(),
            $this->moduleRestrictionRepository
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

    public function testOnlyTheApplePayButtonLeavesTheDeviceCheckToMollie(): void
    {
        $enabledMethod = new \MolPaymentMethod();
        $enabledMethod->enabled = true;
        $this->paymentMethodRepository->method('getPaymentMethodIdByMethodId')->willReturnMap([
            ['applepay', Config::ENVIRONMENT_LIVE, 1, 11],
            ['creditcard', Config::ENVIRONMENT_LIVE, 1, 12],
            ['paypal', Config::ENVIRONMENT_LIVE, 1, 13],
        ]);
        $this->paymentMethodRepository->method('findOneBy')->willReturn($enabledMethod);
        $this->config[Config::MOLLIE_EXPRESS_COMPONENT_PAYPAL] = '1';

        $skippedPerCall = [];
        $restrictionValidation = $this->mock(PaymentMethodRestrictionValidationInterface::class);
        $restrictionValidation->method('isPaymentMethodValid')->willReturnCallback(static function ($method, array $skipped = []) use (&$skippedPerCall) {
            $skippedPerCall[] = $skipped;

            return true;
        });

        $service = new ExpressComponentAvailabilityService(
            $this->configuration,
            $this->context,
            $this->paymentMethodRepository,
            $restrictionValidation,
            $this->subscriptionOrderValidator,
            $this->moduleFactory,
            $this->mock(PrestaLoggerInterface::class),
            $this->minimumPurchaseService(),
            $this->moduleRestrictionRepository
        );

        $this->assertSame(['applepay' => 'automatic', 'googlepay' => 'automatic', 'paypal' => 'automatic'], $service->getButtonVisibility());
        $this->assertSame([[ApplePayPaymentMethodRestrictionValidator::class], [], []], $skippedPerCall);
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
            $this->mock(PrestaLoggerInterface::class),
            $this->minimumPurchaseService(),
            $this->moduleRestrictionRepository
        );

        $this->assertSame([
            'applepay' => 'hidden',
            'googlepay' => 'hidden',
            'paypal' => 'hidden',
        ], $service->getButtonVisibility());
    }

    /**
     * @param array<string, string|false> $placementSettings
     * @param string[] $requiredAddressFields
     *
     * @return ExpressComponentAvailabilityService|\PHPUnit\Framework\MockObject\MockObject
     */
    private function collectService(array $placementSettings = [], array $requiredAddressFields = ['firstname', 'lastname', 'address1', 'city', 'Country:name'])
    {
        $service = $this->getMockBuilder(ExpressComponentAvailabilityService::class)
            ->setConstructorArgs([
                $this->configuration,
                $this->context,
                $this->paymentMethodRepository,
                $this->restrictionValidation,
                $this->subscriptionOrderValidator,
                $this->moduleFactory,
                $this->mock(PrestaLoggerInterface::class),
                $this->minimumPurchaseService(),
                $this->moduleRestrictionRepository,
            ])
            ->setMethods(['getPlacementSetting', 'getRequiredAddressFields', 'getCustomerGroupIds'])
            ->getMock();

        $service->method('getCustomerGroupIds')->willReturn([3, 5]);

        $service->method('getPlacementSetting')->willReturnCallback(static function (string $key) use ($placementSettings) {
            return array_key_exists($key, $placementSettings) ? $placementSettings[$key] : false;
        });
        $service->method('getRequiredAddressFields')->willReturn($requiredAddressFields);

        return $service;
    }

    /**
     * @return \Cart|\PHPUnit\Framework\MockObject\MockObject
     */
    private function anonymousCart(float $totalWithoutShipping = 54.9, bool $hasProducts = true)
    {
        $cart = $this->mock(\Cart::class);
        $cart->id = 21;
        $cart->id_customer = 0;
        $cart->id_address_delivery = 0;
        $cart->id_address_invoice = 0;
        $cart->id_carrier = 0;
        $cart->method('hasProducts')->willReturn($hasProducts);
        $cart->method('getDeliveryOption')->willReturn([]);
        $cart->method('getOrderTotal')->willReturnCallback(static function ($withTaxes, $type) use ($totalWithoutShipping) {
            return \Cart::BOTH_WITHOUT_SHIPPING === $type ? $totalWithoutShipping : $totalWithoutShipping + 5;
        });

        return $cart;
    }

    public function testThePaymentPlacementIsOnUntilTheMerchantSavesIt(): void
    {
        $service = $this->collectService();

        $this->assertTrue($service->isPlacementEnabled(Config::EXPRESS_PLACEMENT_PAYMENT));
        $this->assertFalse($service->isPlacementEnabled(Config::EXPRESS_PLACEMENT_CART));
        $this->assertFalse($service->isPlacementEnabled(Config::EXPRESS_PLACEMENT_CHECKOUT));
    }

    public function testSavedPlacementSwitchesWin(): void
    {
        $service = $this->collectService([
            Config::MOLLIE_EXPRESS_COMPONENT_PLACEMENT_PAYMENT => '0',
            Config::MOLLIE_EXPRESS_COMPONENT_PLACEMENT_CART => '1',
            Config::MOLLIE_EXPRESS_COMPONENT_PLACEMENT_CHECKOUT => '1',
        ]);

        $this->assertFalse($service->isPlacementEnabled(Config::EXPRESS_PLACEMENT_PAYMENT));
        $this->assertTrue($service->isPlacementEnabled(Config::EXPRESS_PLACEMENT_CART));
        $this->assertTrue($service->isPlacementEnabled(Config::EXPRESS_PLACEMENT_CHECKOUT));
    }

    public function testAnUnknownPlacementIsNeverEnabled(): void
    {
        $this->assertFalse($this->collectService()->isPlacementEnabled('product'));
    }

    public function testAnAnonymousCartIsAvailableForCollectWhenGuestsMayCheckOut(): void
    {
        $this->config['PS_GUEST_CHECKOUT_ENABLED'] = '1';
        $this->subscriptionOrderValidator->method('validate')->willReturn(false);
        $this->offerEveryMethod();

        $this->assertTrue($this->collectService()->isAvailableForCollect($this->anonymousCart()));
    }

    public function testACustomersCartIsAvailableForCollectWithoutGuestCheckout(): void
    {
        $this->subscriptionOrderValidator->method('validate')->willReturn(false);
        $this->offerEveryMethod();
        $cart = $this->anonymousCart();
        $cart->id_customer = 3;

        $this->assertTrue($this->collectService()->isAvailableForCollect($cart));
    }

    public function testCollectDoesNotChangeWhatThePaymentStepGets(): void
    {
        $this->config['PS_GUEST_CHECKOUT_ENABLED'] = '1';
        $this->subscriptionOrderValidator->method('validate')->willReturn(false);
        $this->offerEveryMethod();
        $service = $this->collectService();
        $cart = $this->anonymousCart();

        $this->assertTrue($service->isAvailableForCollect($cart));
        $this->assertFalse($service->isAvailable($cart));
        $this->assertSame('cart has no customer', $service->getUnavailableReason());
    }

    /**
     * @dataProvider collectRefusalProvider
     */
    public function testACartIsNotAvailableForCollect(callable $arrange, string $reason): void
    {
        $this->config['PS_GUEST_CHECKOUT_ENABLED'] = '1';
        $this->offerEveryMethod();
        $cart = $this->anonymousCart();
        $requiredAddressFields = ['firstname', 'lastname', 'address1', 'city', 'Country:name', 'postcode', 'id_state', 'State:name', 'id_country'];

        $subscription = false;
        $arrange($this, $cart, $requiredAddressFields, $subscription);
        $this->subscriptionOrderValidator->method('validate')->willReturn($subscription);

        $service = $this->collectService([], $requiredAddressFields);

        $this->assertFalse($service->isAvailableForCollect($cart));
        $this->assertSame($reason, $service->getUnavailableReason());
    }

    public function collectRefusalProvider(): array
    {
        return [
            'express switched off' => [static function (self $test) {
                $test->config[Config::MOLLIE_EXPRESS_COMPONENT_ENABLED] = '0';
            }, 'disabled in settings'],
            'no cart' => [static function (self $test, $cart) {
                $cart->id = 0;
            }, 'cart has no products'],
            'subscription product' => [static function (self $test, $cart, array &$fields, bool &$subscription) {
                $subscription = true;
            }, 'cart contains a subscription product'],
            'every button off' => [static function (self $test) {
                $test->config[Config::MOLLIE_EXPRESS_COMPONENT_APPLEPAY] = '0';
                $test->config[Config::MOLLIE_EXPRESS_COMPONENT_GOOGLEPAY] = '0';
            }, 'no express button is offered for this cart'],
            'minimum purchase' => [static function (self $test, $cart) {
                $test->config['PS_PURCHASE_MINIMUM'] = '500';
                $cart->id_currency = 1;
            }, 'cart is below the minimum purchase total'],
            'no guest checkout' => [static function (self $test) {
                $test->config['PS_GUEST_CHECKOUT_ENABLED'] = '0';
            }, 'visitor cannot become a customer: guest checkout is disabled'],
            'phone required' => [static function (self $test, $cart, array &$fields) {
                $fields[] = 'phone';
            }, 'shop requires address fields the wallet cannot supply: phone'],
            'company and VAT number required' => [static function (self $test, $cart, array &$fields) {
                $fields[] = 'company';
                $fields[] = 'vat_number';
            }, 'shop requires address fields the wallet cannot supply: company, vat_number'],
        ];
    }

    public function testAnEmptyCartIsNotAvailableForCollect(): void
    {
        $this->config['PS_GUEST_CHECKOUT_ENABLED'] = '1';
        $service = $this->collectService();

        $this->assertFalse($service->isAvailableForCollect($this->anonymousCart(54.9, false)));
        $this->assertSame('cart has no products', $service->getUnavailableReason());
    }

    public function testACollectCartWhoseProductsCostNothingIsNotAvailable(): void
    {
        $this->config['PS_GUEST_CHECKOUT_ENABLED'] = '1';
        $this->subscriptionOrderValidator->method('validate')->willReturn(false);
        $this->offerEveryMethod();
        $service = $this->collectService();

        $this->assertFalse($service->isAvailableForCollect($this->anonymousCart(0.0)));
        $this->assertSame('cart total is zero', $service->getUnavailableReason());
    }

    /**
     * @dataProvider paymentStepRestrictionProvider
     */
    public function testAPaymentStepCartTheModuleIsRestrictedForIsNotAvailable(string $restriction, string $reason): void
    {
        $this->subscriptionOrderValidator->method('validate')->willReturn(false);
        $this->offerEveryMethod();
        $this->restrictions[$restriction] = false;

        $service = $this->service();

        $this->assertFalse($service->isAvailable($this->cart));
        $this->assertSame($reason, $service->getUnavailableReason());
    }

    public function paymentStepRestrictionProvider(): array
    {
        return [
            'currency' => ['isCurrencyAllowed', 'currency not allowed for the module'],
            'customer group' => ['isAnyGroupAllowed', 'customer group not allowed for the module'],
            'delivery country' => ['isCountryAllowed', 'delivery country not allowed for the module'],
            'carrier' => ['isCarrierReferenceAllowed', 'carrier not allowed for the module'],
        ];
    }

    public function testThePaymentStepAsksForTheCartsRestrictionValues(): void
    {
        $this->subscriptionOrderValidator->method('validate')->willReturn(false);
        $this->offerEveryMethod();

        $this->assertTrue($this->service()->isAvailable($this->cart));
        $this->assertSame([
            'isCurrencyAllowed' => [[42, 1, 1]],
            'isAnyGroupAllowed' => [[42, 1, [3, 5]]],
            'isCountryAllowed' => [[42, 1, 17]],
            'isCarrierReferenceAllowed' => [[42, 1, 102]],
        ], $this->restrictionCalls);
    }

    public function testAVirtualCartSkipsTheCarrierRestriction(): void
    {
        $cart = $this->mock(\Cart::class);
        $cart->id = 9;
        $cart->id_customer = 3;
        $cart->id_address_delivery = 4;
        $cart->id_address_invoice = 4;
        $cart->id_currency = 1;
        $cart->method('isVirtualCart')->willReturn(true);
        $cart->method('getDeliveryOption')->willReturn([4 => '2,']);
        $cart->method('getOrderTotal')->willReturn(10.8);
        $this->subscriptionOrderValidator->method('validate')->willReturn(false);
        $this->offerEveryMethod();
        $this->restrictions['isCarrierReferenceAllowed'] = false;

        $this->assertTrue($this->service()->isAvailable($cart));
        $this->assertArrayNotHasKey('isCarrierReferenceAllowed', $this->restrictionCalls);
    }

    public function testTheGroupRestrictionIsIgnoredWhileTheShopDoesNotUseGroups(): void
    {
        $this->subscriptionOrderValidator->method('validate')->willReturn(false);
        $this->offerEveryMethod();
        $this->config['PS_GROUP_FEATURE_ACTIVE'] = '0';
        $this->restrictions['isAnyGroupAllowed'] = false;

        $this->assertTrue($this->service()->isAvailable($this->cart));
        $this->assertArrayNotHasKey('isAnyGroupAllowed', $this->restrictionCalls);
    }

    /**
     * @dataProvider collectRestrictionProvider
     */
    public function testACollectCartTheModuleIsRestrictedForIsNotAvailable(string $restriction, string $reason): void
    {
        $this->config['PS_GUEST_CHECKOUT_ENABLED'] = '1';
        $this->subscriptionOrderValidator->method('validate')->willReturn(false);
        $this->offerEveryMethod();
        $this->restrictions[$restriction] = false;
        $service = $this->collectService();

        $this->assertFalse($service->isAvailableForCollect($this->anonymousCart()));
        $this->assertSame($reason, $service->getUnavailableReason());
    }

    public function collectRestrictionProvider(): array
    {
        return [
            'currency' => ['isCurrencyAllowed', 'currency not allowed for the module'],
            'customer group' => ['isAnyGroupAllowed', 'customer group not allowed for the module'],
        ];
    }

    public function testAnAnonymousCollectCartIsJudgedAsAGuest(): void
    {
        $this->config['PS_GUEST_CHECKOUT_ENABLED'] = '1';
        $this->subscriptionOrderValidator->method('validate')->willReturn(false);
        $this->offerEveryMethod();
        $this->assertTrue($this->collectService()->isAvailableForCollect($this->anonymousCart()));
        $this->assertSame([[42, 1, [2]]], $this->restrictionCalls['isAnyGroupAllowed']);
    }

    public function testACustomersCollectCartIsJudgedByTheCustomersGroups(): void
    {
        $this->subscriptionOrderValidator->method('validate')->willReturn(false);
        $this->offerEveryMethod();
        $cart = $this->anonymousCart();
        $cart->id_customer = 3;

        $this->assertTrue($this->collectService()->isAvailableForCollect($cart));
        $this->assertSame([[42, 1, [3, 5]]], $this->restrictionCalls['isAnyGroupAllowed']);
    }

    public function testCollectLeavesTheCountryAndCarrierToTheShippingCallback(): void
    {
        $this->config['PS_GUEST_CHECKOUT_ENABLED'] = '1';
        $this->subscriptionOrderValidator->method('validate')->willReturn(false);
        $this->offerEveryMethod();
        $this->restrictions['isCountryAllowed'] = false;
        $this->restrictions['isCarrierReferenceAllowed'] = false;

        $this->assertTrue($this->collectService()->isAvailableForCollect($this->anonymousCart()));
        $this->assertArrayNotHasKey('isCountryAllowed', $this->restrictionCalls);
        $this->assertArrayNotHasKey('isCarrierReferenceAllowed', $this->restrictionCalls);
    }
}
