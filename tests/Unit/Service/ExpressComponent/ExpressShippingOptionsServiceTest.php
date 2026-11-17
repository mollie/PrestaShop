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

use Mollie\Exception\ExpressShippingRejectedException;
use Mollie\Factory\ModuleFactory;
use Mollie\Repository\ModuleRestrictionRepositoryInterface;
use Mollie\Repository\MolExpressSessionRepositoryInterface;
use Mollie\Service\ExpressComponent\ExpressComponentAvailabilityService;
use Mollie\Service\ExpressComponent\ExpressQuoteCustomerService;
use Mollie\Service\ExpressComponent\ExpressShippingOptionsService;
use Mollie\Service\MinimumPurchaseService;
use Mollie\Tests\Unit\BaseTestCase;

class ExpressShippingOptionsServiceTest extends BaseTestCase
{
    const ADDRESS_ID = 41;
    const COUNTRY_ID = 13;
    const SESSION_AMOUNT = 50.0;
    const QUOTE_CUSTOMER_ID = 900;

    const ADDRESS = [
        'postalCode' => '1015',
        'city' => 'Amsterdam',
        'region' => '',
        'country' => 'NL',
    ];

    /** @var MolExpressSessionRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $sessionRepository;
    /** @var MinimumPurchaseService|\PHPUnit\Framework\MockObject\MockObject */
    private $minimumPurchaseService;
    /** @var ExpressComponentAvailabilityService|\PHPUnit\Framework\MockObject\MockObject */
    private $availability;
    /** @var ModuleRestrictionRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $moduleRestrictionRepository;
    /** @var ModuleFactory|\PHPUnit\Framework\MockObject\MockObject */
    private $moduleFactory;
    /** @var ExpressQuoteCustomerService|\PHPUnit\Framework\MockObject\MockObject */
    private $quoteCustomerService;
    /** @var int[] the cart customer each time a carrier was priced */
    private $pricedForCustomerIds = [];
    /** @var bool */
    private $countryAllowed = true;
    /** @var int[] carrier references the module is not allowed for */
    private $disallowedCarrierReferences = [];
    /** @var ExpressShippingOptionsService|\PHPUnit\Framework\MockObject\MockObject */
    private $service;
    /** @var float */
    private $productTotal = self::SESSION_AMOUNT;
    /** @var array<int, float> carrier id => shipping price */
    private $shippingPrices = [];
    /** @var array<int, float> carrier id => cart total with that carrier, when a cart rule makes it differ from products + shipping */
    private $mismatchedTotals = [];

    protected function setUp(): void
    {
        parent::setUp();

        $session = new \MolExpressSession();
        $session->amount = self::SESSION_AMOUNT;
        $session->collect_details = true;

        $this->sessionRepository = $this->mock(MolExpressSessionRepositoryInterface::class);
        $this->sessionRepository->method('findLatestByCartId')->willReturn($session);

        $this->minimumPurchaseService = $this->mock(MinimumPurchaseService::class);
        $this->minimumPurchaseService->method('getNotReachedMessage')->willReturn('');

        $this->availability = $this->mock(ExpressComponentAvailabilityService::class);
        $this->availability->method('getButtonVisibility')->willReturn([
            'applepay' => ExpressComponentAvailabilityService::VISIBILITY_AUTOMATIC,
            'googlepay' => ExpressComponentAvailabilityService::VISIBILITY_HIDDEN,
            'paypal' => ExpressComponentAvailabilityService::VISIBILITY_HIDDEN,
        ]);

        $this->context->method('getCurrencyIso')->willReturn('EUR');
        $this->context->method('getShopId')->willReturn(1);

        $module = $this->mock(\Mollie::class);
        $module->id = 42;
        $this->moduleFactory = $this->mock(ModuleFactory::class);
        $this->moduleFactory->method('getModule')->willReturn($module);

        $this->moduleRestrictionRepository = $this->mock(ModuleRestrictionRepositoryInterface::class);
        $this->moduleRestrictionRepository->method('isCountryAllowed')->willReturnCallback(function () {
            return $this->countryAllowed;
        });
        $this->moduleRestrictionRepository->method('isCarrierReferenceAllowed')->willReturnCallback(function ($moduleId, $shopId, $reference) {
            return !in_array($reference, $this->disallowedCarrierReferences, true);
        });

        $this->quoteCustomerService = $this->mock(ExpressQuoteCustomerService::class);
        $this->quoteCustomerService->method('getCustomerId')->willReturn(self::QUOTE_CUSTOMER_ID);

        $this->cart->id = 7;
        $this->cart->id_customer = 0;
        $this->cart->delivery_option = '{"3":"1,"}';
        $this->cart->method('getOrderTotal')->willReturnCallback(function ($withTaxes, $type) {
            return $this->getOrderTotal((int) $type);
        });

        $this->service = $this->getMockBuilder(ExpressShippingOptionsService::class)
            ->setConstructorArgs([
                $this->sessionRepository,
                $this->minimumPurchaseService,
                $this->availability,
                $this->context,
                $this->moduleRestrictionRepository,
                $this->moduleFactory,
                $this->quoteCustomerService,
            ])
            ->setMethods(['findCountry', 'needsDni', 'findStateId', 'applyPlaceholderAddress', 'getUsableCarriers'])
            ->getMock();

        $this->service->method('findCountry')->willReturn($this->country(true, false));
        $this->service->method('needsDni')->willReturn(false);
        $this->service->method('applyPlaceholderAddress')->willReturn(self::ADDRESS_ID);
    }

    public function testItRejectsAnInactiveCountry(): void
    {
        $service = $this->serviceWithCountry($this->country(false, false));

        $this->assertRejected(ExpressShippingRejectedException::UNSUPPORTED_COUNTRY, $service);
    }

    public function testItRejectsAnUnknownCountry(): void
    {
        $service = $this->serviceWithCountry(null);

        $this->assertRejected(ExpressShippingRejectedException::UNSUPPORTED_COUNTRY, $service);
    }

    public function testItRejectsAnAddressWithoutACountryCode(): void
    {
        $service = $this->getMockBuilder(ExpressShippingOptionsService::class)
            ->setConstructorArgs([$this->sessionRepository, $this->minimumPurchaseService, $this->availability, $this->context, $this->moduleRestrictionRepository, $this->moduleFactory, $this->quoteCustomerService])
            ->setMethods(['needsDni', 'findStateId', 'applyPlaceholderAddress', 'getUsableCarriers'])
            ->getMock();
        $service->expects($this->never())->method('applyPlaceholderAddress');

        $address = self::ADDRESS;
        unset($address['country']);

        $this->assertRejected(ExpressShippingRejectedException::UNSUPPORTED_COUNTRY, $service, $address);
    }

    public function testItRejectsACountryTheModuleIsNotAllowedFor(): void
    {
        $this->countryAllowed = false;
        $this->moduleRestrictionRepository->expects($this->once())->method('isCountryAllowed')->with(42, 1, self::COUNTRY_ID);
        $this->service->expects($this->never())->method('applyPlaceholderAddress');

        $this->assertRejected(ExpressShippingRejectedException::UNSUPPORTED_COUNTRY, $this->service);
    }

    public function testItAcceptsACountryTheModuleIsAllowedFor(): void
    {
        $this->shippingPrices = [3 => 4.99];
        $this->service->method('getUsableCarriers')->willReturn([$this->carrierRow(3, 103, 'PostNL')]);

        $this->assertCount(1, $this->service->getOptions($this->cart, self::ADDRESS));
    }

    public function testItDropsACarrierTheModuleIsNotAllowedFor(): void
    {
        $this->shippingPrices = [3 => 4.99, 4 => 6.5];
        $this->disallowedCarrierReferences = [104];
        $this->service->method('getUsableCarriers')->willReturn([
            $this->carrierRow(3, 103, 'PostNL'),
            $this->carrierRow(4, 104, 'DHL'),
        ]);

        $options = $this->service->getOptions($this->cart, self::ADDRESS);

        $this->assertSame(['103'], array_column($options, 'reference'));
    }

    public function testItRejectsWhenTheModuleIsAllowedForNoUsableCarrier(): void
    {
        $this->shippingPrices = [4 => 6.5];
        $this->disallowedCarrierReferences = [104];
        $this->service->method('getUsableCarriers')->willReturn([$this->carrierRow(4, 104, 'DHL')]);

        $this->assertRejected(ExpressShippingRejectedException::UNSUPPORTED_ADDRESS, $this->service);
    }

    public function testItRejectsACountryThatNeedsADni(): void
    {
        $service = $this->partialService();
        $service->method('findCountry')->willReturn($this->country(true, false));
        $service->method('needsDni')->willReturn(true);
        $service->expects($this->never())->method('applyPlaceholderAddress');

        $this->assertRejected(ExpressShippingRejectedException::UNSUPPORTED_COUNTRY, $service);
    }

    public function testItRejectsARegionThatMapsToNoState(): void
    {
        $service = $this->serviceWithCountry($this->country(true, true));
        $service->method('findStateId')->willReturn(0);
        $service->expects($this->never())->method('applyPlaceholderAddress');

        $this->assertRejected(ExpressShippingRejectedException::UNSUPPORTED_ADDRESS, $service, ['region' => 'Nowhere'] + self::ADDRESS);
    }

    public function testItStoresTheMappedStateOnThePlaceholderAddress(): void
    {
        $this->shippingPrices = [5 => 3.99];

        $service = $this->serviceWithCountry($this->country(true, true));
        $service->method('findStateId')->with('CA', self::COUNTRY_ID)->willReturn(9);
        $service->expects($this->once())
            ->method('applyPlaceholderAddress')
            ->with($this->cart, self::COUNTRY_ID, 9)
            ->willReturn(self::ADDRESS_ID);
        $service->method('getUsableCarriers')->willReturn([$this->carrierRow(5, 105, 'Courier')]);

        $options = $service->getOptions($this->cart, ['region' => 'CA', 'country' => 'US'] + self::ADDRESS);

        $this->assertCount(1, $options);
    }

    public function testItRejectsAPostcodeTheShopFormatRefuses(): void
    {
        $service = $this->serviceWithCountry($this->countryWithPostcodeFormat('NNNNN', 'LT'));
        $service->expects($this->never())->method('applyPlaceholderAddress');

        $this->assertRejected(ExpressShippingRejectedException::UNSUPPORTED_POSTAL_CODE, $service, ['postalCode' => '4741', 'country' => 'LT'] + self::ADDRESS);
    }

    public function testThePlaceholderGetsThePostcodeInTheShopFormat(): void
    {
        $this->shippingPrices = [5 => 3.99];

        $service = $this->serviceWithCountry($this->countryWithPostcodeFormat('NNNNN', 'LT'));
        $service->expects($this->once())
            ->method('applyPlaceholderAddress')
            ->with($this->cart, self::COUNTRY_ID, 0, $this->callback(static function (array $address) {
                return '47412' === $address['postalCode'];
            }))
            ->willReturn(self::ADDRESS_ID);
        $service->method('getUsableCarriers')->willReturn([$this->carrierRow(5, 105, 'Courier')]);

        $this->assertCount(1, $service->getOptions($this->cart, ['postalCode' => 'LT-47412', 'country' => 'LT'] + self::ADDRESS));
    }

    /**
     * @dataProvider uncheckedPostcodeProvider
     */
    public function testItDoesNotJudgeAPostcodeTheWalletShortensOrLeavesOut(string $iso, string $postcode): void
    {
        $this->shippingPrices = [5 => 3.99];

        $service = $this->serviceWithCountry($this->countryWithPostcodeFormat('LLNN NLL', $iso));
        $service->method('applyPlaceholderAddress')->willReturn(self::ADDRESS_ID);
        $service->method('getUsableCarriers')->willReturn([$this->carrierRow(5, 105, 'Courier')]);

        $this->assertCount(1, $service->getOptions($this->cart, ['postalCode' => $postcode, 'country' => $iso] + self::ADDRESS));
    }

    public function uncheckedPostcodeProvider(): array
    {
        return [
            'Apple Pay sends only the outward code in the UK' => ['GB', 'SW1A'],
            'Apple Pay sends only the first part in Canada' => ['CA', 'K1A'],
            'no postcode in the sheet yet' => ['NL', ''],
        ];
    }

    public function testItRejectsWhenTheProductTotalForTheAddressDiffersFromTheSession(): void
    {
        $this->productTotal = 52.1;
        $this->service->expects($this->never())->method('getUsableCarriers');

        $this->assertRejected(ExpressShippingRejectedException::UNSUPPORTED_COUNTRY, $this->service);
    }

    public function testItRejectsACartBelowTheMinimumPurchase(): void
    {
        $minimumPurchaseService = $this->mock(MinimumPurchaseService::class);
        $minimumPurchaseService->method('getNotReachedMessage')->willReturn('A minimum shopping cart total of 60 is required');
        $service = $this->partialService(['minimumPurchaseService' => $minimumPurchaseService]);
        $service->method('findCountry')->willReturn($this->country(true, false));
        $service->method('applyPlaceholderAddress')->willReturn(self::ADDRESS_ID);
        $service->expects($this->never())->method('getUsableCarriers');

        $this->assertRejected(ExpressShippingRejectedException::UNSUPPORTED_ADDRESS, $service);
    }

    public function testItRejectsWhenNoExpressButtonIsOfferedForTheAddress(): void
    {
        $availability = $this->mock(ExpressComponentAvailabilityService::class);
        $availability->method('getButtonVisibility')->willReturn([
            'applepay' => ExpressComponentAvailabilityService::VISIBILITY_HIDDEN,
            'googlepay' => ExpressComponentAvailabilityService::VISIBILITY_HIDDEN,
            'paypal' => ExpressComponentAvailabilityService::VISIBILITY_HIDDEN,
        ]);
        $service = $this->partialService(['availability' => $availability]);
        $service->method('findCountry')->willReturn($this->country(true, false));
        $service->method('applyPlaceholderAddress')->willReturn(self::ADDRESS_ID);
        $service->expects($this->never())->method('getUsableCarriers');

        $this->assertRejected(ExpressShippingRejectedException::UNSUPPORTED_COUNTRY, $service);
    }

    public function testItRejectsAnAddressNoCarrierServes(): void
    {
        $this->service->method('getUsableCarriers')->willReturn([]);

        $this->assertRejected(ExpressShippingRejectedException::UNSUPPORTED_ADDRESS, $this->service);
    }

    public function testItDropsACarrierWhoseTotalIsBelowTheSessionAmount(): void
    {
        $this->shippingPrices = [3 => 4.99, 4 => 6.5];
        // a carrier bound cart rule discounts more than the carrier's shipping
        $this->mismatchedTotals = [4 => 48.0];
        $this->service->method('getUsableCarriers')->willReturn([
            $this->carrierRow(3, 103, 'PostNL'),
            $this->carrierRow(4, 104, 'DHL'),
        ]);

        $options = $this->service->getOptions($this->cart, self::ADDRESS);

        $this->assertSame(['103'], array_column($options, 'reference'));
    }

    public function testItRejectsWhenEveryCarrierIsDropped(): void
    {
        $this->shippingPrices = [4 => 6.5];
        $this->mismatchedTotals = [4 => 49.99];
        $this->service->method('getUsableCarriers')->willReturn([$this->carrierRow(4, 104, 'DHL')]);

        $this->assertRejected(ExpressShippingRejectedException::UNSUPPORTED_ADDRESS, $this->service);
    }

    public function testItBuildsOneOptionPerUsableCarrier(): void
    {
        $this->shippingPrices = [3 => 4.99, 6 => 0.0];
        $this->service->method('getUsableCarriers')->willReturn([
            $this->carrierRow(3, 103, 'PostNL', 'Delivery next day!'),
            $this->carrierRow(6, 106, 'Pick up in store', ''),
        ]);

        $options = $this->service->getOptions($this->cart, self::ADDRESS);

        $this->assertSame([
            [
                'reference' => '103',
                'description' => 'PostNL (Delivery next day!)',
                'amount' => ['currency' => 'EUR', 'value' => '4.99'],
            ],
            [
                'reference' => '106',
                'description' => 'Pick up in store',
                'amount' => ['currency' => 'EUR', 'value' => '0.00'],
            ],
        ], $options);
    }

    public function testAFreeShippingCartRuleQuotesTheCarrierAtZero(): void
    {
        $this->shippingPrices = [3 => 6.5];
        $this->mismatchedTotals = [3 => self::SESSION_AMOUNT];
        $this->service->method('getUsableCarriers')->willReturn([$this->carrierRow(3, 103, 'PostNL')]);

        $options = $this->service->getOptions($this->cart, self::ADDRESS);

        $this->assertSame(['currency' => 'EUR', 'value' => '0.00'], $options[0]['amount']);
    }

    public function testTheOptionIsQuotedAsTheDifferenceToTheCartTotal(): void
    {
        $this->shippingPrices = [3 => 6.5];
        // a cart rule halves the shipping of this carrier
        $this->mismatchedTotals = [3 => 53.25];
        $this->service->method('getUsableCarriers')->willReturn([$this->carrierRow(3, 103, 'PostNL')]);

        $options = $this->service->getOptions($this->cart, self::ADDRESS);

        $this->assertSame('3.25', $options[0]['amount']['value']);
    }

    public function testItRestoresTheCartDeliveryOptionAfterPricing(): void
    {
        $this->shippingPrices = [3 => 4.99];
        $this->service->method('getUsableCarriers')->willReturn([$this->carrierRow(3, 103, 'PostNL')]);

        $this->service->getOptions($this->cart, self::ADDRESS);

        $this->assertSame('{"3":"1,"}', $this->cart->delivery_option);
    }

    public function testAnAnonymousCartIsQuotedForTheGuestGroupAndKeepsNoCustomer(): void
    {
        $this->shippingPrices = [3 => 4.99];
        $listedForCustomerId = null;
        $this->service->method('getUsableCarriers')->willReturnCallback(function (\Cart $cart) use (&$listedForCustomerId) {
            $listedForCustomerId = (int) $cart->id_customer;

            return [$this->carrierRow(3, 103, 'PostNL')];
        });

        $this->service->getOptions($this->cart, self::ADDRESS);

        $this->assertSame(self::QUOTE_CUSTOMER_ID, $listedForCustomerId);
        $this->assertSame([self::QUOTE_CUSTOMER_ID], $this->pricedForCustomerIds);
        $this->assertSame(0, (int) $this->cart->id_customer);
    }

    public function testACartWithACustomerIsQuotedForThatCustomer(): void
    {
        $this->cart->id_customer = 15;
        $this->shippingPrices = [3 => 4.99];
        $this->quoteCustomerService->expects($this->never())->method('getCustomerId');
        $listedForCustomerId = null;
        $this->service->method('getUsableCarriers')->willReturnCallback(function (\Cart $cart) use (&$listedForCustomerId) {
            $listedForCustomerId = (int) $cart->id_customer;

            return [$this->carrierRow(3, 103, 'PostNL')];
        });

        $this->service->getOptions($this->cart, self::ADDRESS);

        $this->assertSame(15, $listedForCustomerId);
        $this->assertSame(15, (int) $this->cart->id_customer);
    }

    public function testTheCartIsRestoredWhenQuotingFails(): void
    {
        $this->service->method('getUsableCarriers')->willThrowException(new \PrestaShopException('carrier list failed'));

        try {
            $this->service->getOptions($this->cart, self::ADDRESS);
            $this->fail('The exception was swallowed');
        } catch (\PrestaShopException $exception) {
            $this->assertSame(0, (int) $this->cart->id_customer);
            $this->assertSame('{"3":"1,"}', $this->cart->delivery_option);
        }
    }

    private function getOrderTotal(int $type): float
    {
        if (\Cart::BOTH_WITHOUT_SHIPPING === $type) {
            return $this->productTotal;
        }

        $this->pricedForCustomerIds[] = (int) $this->cart->id_customer;
        $carrierId = (int) current((array) json_decode((string) $this->cart->delivery_option, true));
        $shipping = $this->shippingPrices[$carrierId] ?? 0.0;

        return $this->mismatchedTotals[$carrierId] ?? $this->productTotal + $shipping;
    }

    private function assertRejected(string $type, ExpressShippingOptionsService $service, array $address = self::ADDRESS): void
    {
        try {
            $service->getOptions($this->cart, $address);
        } catch (ExpressShippingRejectedException $exception) {
            $this->assertSame($type, $exception->getType());

            return;
        }

        $this->fail('The address was not rejected');
    }

    /**
     * @return ExpressShippingOptionsService|\PHPUnit\Framework\MockObject\MockObject
     */
    private function serviceWithCountry(?\Country $country)
    {
        $service = $this->partialService();
        $service->method('findCountry')->willReturn($country);
        $service->method('needsDni')->willReturn(false);

        return $service;
    }

    /**
     * @return ExpressShippingOptionsService|\PHPUnit\Framework\MockObject\MockObject
     */
    private function partialService(array $dependencies = [])
    {
        return $this->getMockBuilder(ExpressShippingOptionsService::class)
            ->setConstructorArgs([
                $this->sessionRepository,
                $dependencies['minimumPurchaseService'] ?? $this->minimumPurchaseService,
                $dependencies['availability'] ?? $this->availability,
                $this->context,
                $this->moduleRestrictionRepository,
                $this->moduleFactory,
                $this->quoteCustomerService,
            ])
            ->setMethods(['findCountry', 'needsDni', 'findStateId', 'applyPlaceholderAddress', 'getUsableCarriers'])
            ->getMock();
    }

    private function countryWithPostcodeFormat(string $format, string $iso): \Country
    {
        $country = $this->country(true, false);
        $country->need_zip_code = true;
        $country->zip_code_format = $format;
        $country->iso_code = $iso;
        $country->method('checkZipCode')->willReturnCallback(static function ($postcode) use ($format, $iso) {
            $regexp = str_replace(['N', 'L', 'C'], ['[0-9]', '[a-zA-Z]', $iso], $format);

            return (bool) preg_match('/^' . $regexp . '$/ui', (string) $postcode);
        });

        return $country;
    }

    private function country(bool $active, bool $containsStates): \Country
    {
        $country = $this->mock(\Country::class);
        $country->id = self::COUNTRY_ID;
        $country->active = $active;
        $country->contains_states = $containsStates;

        return $country;
    }

    private function carrierRow(int $carrierId, int $referenceId, string $name, string $delay = ''): array
    {
        return [
            'id_carrier' => $carrierId,
            'id_reference' => $referenceId,
            'name' => $name,
            'delay' => $delay,
        ];
    }
}
