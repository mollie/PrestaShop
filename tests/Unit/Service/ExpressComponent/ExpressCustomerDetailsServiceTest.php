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

use Mollie\Adapter\API\MollieSessionApiAdapterInterface;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\DTO\Express\ExpressSession;
use Mollie\Exception\ExpressCustomerDetailsException;
use Mollie\Exception\GuestCheckoutNotAvailableException;
use Mollie\Exception\RetryOverException;
use Mollie\Handler\RetryHandlerInterface;
use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Repository\MolExpressSessionRepositoryInterface;
use Mollie\Service\ExpressComponent\ExpressCustomerDetailsService;
use Mollie\Tests\Unit\BaseTestCase;

class ExpressCustomerDetailsServiceTest extends BaseTestCase
{
    /** @var MolExpressSessionRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $sessionRepository;
    /** @var MollieSessionApiAdapterInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $sessionApi;
    /** @var RetryHandlerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $retryHandler;
    /** @var array */
    private $calls;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sessionRepository = $this->mock(MolExpressSessionRepositoryInterface::class);
        $this->sessionApi = $this->mock(MollieSessionApiAdapterInterface::class);
        $this->retryHandler = $this->mock(RetryHandlerInterface::class);
        $this->configuration = $this->mock(ConfigurationAdapter::class);
        $this->configuration->method('get')->willReturnCallback(function ($key) {
            return 'PS_GUEST_CHECKOUT_ENABLED' === $key ? '1' : null;
        });

        $this->calls = [
            'guests' => [],
            'addresses' => [],
            'reusable' => [],
            'carriers' => ['7' => 21],
            'customer' => null,
        ];

        $this->cart->id = 41;
        $this->cart->id_shop = 1;
        $this->cart->id_customer = 0;
        $this->cart->id_address_delivery = 0;
        $this->cart->id_address_invoice = 0;
        $this->cart->secure_key = 'cart_secure_key_fake';
        $this->cart->method('getProducts')->willReturn([['id_address_delivery' => 0]]);
    }

    public function testItMapsMollieAddressFieldsToAShopAddress(): void
    {
        $fields = $this->service()->buildAddressFields([
            'givenName' => ' Jane ',
            'familyName' => 'Customer',
            'email' => 'jane.customer@example.test',
            'streetAndNumber' => 'Keizersgracht 126',
            'streetAdditional' => 'Floor 2',
            'city' => 'Amsterdam',
            'region' => 'Noord-Holland',
            'postalCode' => '1015 CW',
            'country' => 'nl',
            'phone' => '+31208202070',
        ], ['firstname' => 'Fallback', 'lastname' => 'Name']);

        $this->assertSame([
            'firstname' => 'Jane',
            'lastname' => 'Customer',
            'address1' => 'Keizersgracht 126',
            'address2' => 'Floor 2',
            'city' => 'Amsterdam',
            'postcode' => '1015 CW',
            'country' => 'NL',
            'region' => 'Noord-Holland',
            'phone' => '+31208202070',
        ], $fields);
    }

    public function testAnAddressWithoutNamesTakesTheCustomerNames(): void
    {
        $fields = $this->service()->buildAddressFields(['streetAndNumber' => 'Main 1', 'country' => 'NL'], ['firstname' => 'Jane', 'lastname' => 'Customer']);

        $this->assertSame('Jane', $fields['firstname']);
        $this->assertSame('Customer', $fields['lastname']);
        $this->assertSame('', $fields['phone']);
    }

    public function testCustomerNamesFallBackToTheShippingAddress(): void
    {
        $fields = $this->service()->buildCustomerFields(
            ['email' => 'jane.customer@example.test', 'country' => 'NL'],
            ['givenName' => 'Jane', 'familyName' => 'Customer']
        );

        $this->assertSame(['firstname' => 'Jane', 'lastname' => 'Customer', 'email' => 'jane.customer@example.test'], $fields);
    }

    public function testAnAnonymousCartGetsAGuestBothAddressesAndTheCarrier(): void
    {
        $this->cart->method('isVirtualCart')->willReturn(false);
        $this->cart->expects($this->once())->method('updateAddressId')->with(0, 501);
        $this->cart->expects($this->once())->method('setDeliveryOption')->with([501 => '21,']);

        $this->service()->applyToCart($this->cart, $this->payment($this->billing(), $this->shipping(), '7'));

        $this->assertCount(1, $this->calls['guests']);
        $this->assertSame(['firstname' => 'Jane', 'lastname' => 'Customer', 'email' => 'jane.customer@example.test'], $this->calls['guests'][0]);
        $this->assertSame('Shipstraat 2', $this->calls['addresses'][0]['address1']);
        $this->assertSame('Billweg 1', $this->calls['addresses'][1]['address1']);
        $this->assertSame(900, (int) $this->cart->id_customer);
        $this->assertSame(501, (int) $this->cart->id_address_delivery);
        $this->assertSame(502, (int) $this->cart->id_address_invoice);
        $this->assertSame(21, (int) $this->cart->id_carrier);
        $this->assertSame(900, (int) $this->calls['customer']->id);
    }

    public function testABillingAddressWithoutStreetUsesTheShippingAddress(): void
    {
        $this->cart->method('isVirtualCart')->willReturn(false);

        $billing = ['givenName' => 'Jane', 'familyName' => 'Customer', 'email' => 'jane.customer@example.test', 'postalCode' => '1000 AA', 'country' => 'NL'];

        $this->service()->applyToCart($this->cart, $this->payment($billing, $this->shipping(), '7'));

        $this->assertCount(1, $this->calls['addresses']);
        $this->assertSame('Shipstraat 2', $this->calls['addresses'][0]['address1']);
        $this->assertSame(501, (int) $this->cart->id_address_delivery);
        $this->assertSame(501, (int) $this->cart->id_address_invoice);
    }

    public function testAVirtualCartUsesTheBillingAddressForBothAndNoCarrier(): void
    {
        $this->cart->method('isVirtualCart')->willReturn(true);
        $this->cart->expects($this->never())->method('setDeliveryOption');

        $this->service()->applyToCart($this->cart, $this->payment($this->billing(), null, null));

        $this->assertCount(1, $this->calls['addresses']);
        $this->assertSame('Billweg 1', $this->calls['addresses'][0]['address1']);
        $this->assertSame(501, (int) $this->cart->id_address_delivery);
        $this->assertSame(501, (int) $this->cart->id_address_invoice);
    }

    public function testAShippableCartWithoutShippingOptionFails(): void
    {
        $this->cart->method('isVirtualCart')->willReturn(false);
        $this->sessionRepository->method('findLatestByCartId')->willReturn($this->storedSession());
        $this->retryHandler->method('retry')->willReturnCallback(function (callable $function) {
            return $function();
        });
        $this->sessionApi->method('get')->willReturn($this->session($this->billing(), $this->shipping(), null));

        $this->expectException(ExpressCustomerDetailsException::class);
        $this->expectExceptionCode(ExpressCustomerDetailsException::SHIPPING_OPTION_MISSING);

        $this->service()->applyToCart($this->cart, $this->payment($this->billing(), $this->shipping(), null));
    }

    public function testAnUnknownShippingOptionFails(): void
    {
        $this->cart->method('isVirtualCart')->willReturn(false);

        $this->expectException(ExpressCustomerDetailsException::class);
        $this->expectExceptionCode(ExpressCustomerDetailsException::CARRIER_NOT_FOUND);

        $this->service()->applyToCart($this->cart, $this->payment($this->billing(), $this->shipping(), '99'));
    }

    public function testMissingDetailsAreReadFromTheSession(): void
    {
        $this->cart->method('isVirtualCart')->willReturn(false);
        $this->sessionRepository->method('findLatestByCartId')->willReturn($this->storedSession());
        $this->retryHandler->expects($this->once())->method('retry')->willReturnCallback(function (callable $function, array $options) {
            $this->assertSame(ExpressCustomerDetailsException::class, $options['accepted_exception']);

            return $function();
        });
        $this->sessionApi->expects($this->once())->method('get')->with('sess_fake')->willReturn($this->session($this->billing(), $this->shipping(), '7'));

        $this->service()->applyToCart($this->cart, $this->payment(null, null, null));

        $this->assertSame(21, (int) $this->cart->id_carrier);
        $this->assertSame('Billweg 1', $this->calls['addresses'][1]['address1']);
    }

    public function testDetailsStillMissingAfterTheRetriesFail(): void
    {
        $this->cart->method('isVirtualCart')->willReturn(false);
        $this->sessionRepository->method('findLatestByCartId')->willReturn($this->storedSession());
        $this->sessionApi->method('get')->willReturn($this->session(null, null, null));
        $this->retryHandler->method('retry')->willReturnCallback(function (callable $function) {
            try {
                $function();
            } catch (ExpressCustomerDetailsException $exception) {
                throw new RetryOverException();
            }
        });

        $this->expectException(ExpressCustomerDetailsException::class);
        $this->expectExceptionCode(ExpressCustomerDetailsException::DETAILS_MISSING);

        $this->service()->applyToCart($this->cart, $this->payment(null, null, null));
    }

    public function testDetailsMissingWithoutAStoredSessionFail(): void
    {
        $this->cart->method('isVirtualCart')->willReturn(false);
        $this->sessionRepository->method('findLatestByCartId')->willReturn(null);

        $this->expectException(ExpressCustomerDetailsException::class);
        $this->expectExceptionCode(ExpressCustomerDetailsException::DETAILS_MISSING);

        $this->service()->applyToCart($this->cart, $this->payment(null, null, null));
    }

    public function testTheCartCustomerIsKept(): void
    {
        $this->cart->id_customer = 300;
        $this->cart->method('isVirtualCart')->willReturn(false);

        $this->service()->applyToCart($this->cart, $this->payment($this->billing(), $this->shipping(), '7'));

        $this->assertSame([], $this->calls['guests']);
        $this->assertSame(300, (int) $this->cart->id_customer);
        $this->assertSame('Registered', $this->calls['customer']->firstname);
    }

    public function testExpressAddressesOfAnEarlierAttemptAreReused(): void
    {
        $this->cart->id_customer = 900;
        $this->cart->id_address_delivery = 61;
        $this->cart->id_address_invoice = 62;
        $this->calls['reusable'] = [61, 62];
        $this->cart->method('isVirtualCart')->willReturn(false);

        $this->service()->applyToCart($this->cart, $this->payment($this->billing(), $this->shipping(), '7'));

        $this->assertSame([], $this->calls['addresses']);
        $this->assertSame(61, (int) $this->cart->id_address_delivery);
        $this->assertSame(62, (int) $this->cart->id_address_invoice);
    }

    public function testGuestCheckoutOffRefusesAnAnonymousCart(): void
    {
        $this->configuration = $this->mock(ConfigurationAdapter::class);
        $this->configuration->method('get')->willReturn(null);
        $this->cart->method('isVirtualCart')->willReturn(false);

        $this->expectException(GuestCheckoutNotAvailableException::class);

        $this->service()->applyToCart($this->cart, $this->payment($this->billing(), $this->shipping(), '7'));
    }

    private function billing(): array
    {
        return [
            'givenName' => 'Jane',
            'familyName' => 'Customer',
            'email' => 'jane.customer@example.test',
            'streetAndNumber' => 'Billweg 1',
            'city' => 'Utrecht',
            'postalCode' => '3511 AA',
            'country' => 'NL',
        ];
    }

    private function shipping(): array
    {
        return [
            'givenName' => 'John',
            'familyName' => 'Receiver',
            'streetAndNumber' => 'Shipstraat 2',
            'city' => 'Amsterdam',
            'postalCode' => '1011 AB',
            'country' => 'NL',
        ];
    }

    private function payment(?array $billing, ?array $shipping, ?string $reference): \stdClass
    {
        $payment = new \stdClass();
        $payment->id = 'tr_fake';
        $payment->billingAddress = null === $billing ? null : (object) $billing;
        $payment->shippingAddress = null === $shipping ? null : (object) $shipping;
        $payment->lines = [(object) ['type' => 'physical', 'sku' => 'demo_1']];

        if (null !== $reference) {
            $payment->lines[] = (object) ['type' => 'shipping_fee', 'reference' => $reference];
        }

        return $payment;
    }

    private function session(?array $billing, ?array $shipping, ?string $reference): ExpressSession
    {
        return new ExpressSession('sess_fake', 'token_fake', ExpressSession::STATUS_COMPLETED, '10.00', 'EUR', '2026-11-16T10:00:00+00:00', 'mol_41', $billing, $shipping, $reference);
    }

    private function storedSession(): \MolExpressSession
    {
        $stored = $this->mock(\MolExpressSession::class);
        $stored->session_id = 'sess_fake';
        $stored->collect_details = true;

        return $stored;
    }

    private function service(): ExpressCustomerDetailsService
    {
        return new class($this->sessionRepository, $this->sessionApi, $this->retryHandler, $this->configuration, $this->mock(PrestaLoggerInterface::class), $this->calls) extends ExpressCustomerDetailsService {
            /** @var array */
            public $calls;

            public function __construct(
                MolExpressSessionRepositoryInterface $sessionRepository,
                MollieSessionApiAdapterInterface $sessionApi,
                RetryHandlerInterface $retryHandler,
                ConfigurationAdapter $configuration,
                PrestaLoggerInterface $logger,
                array &$calls
            ) {
                parent::__construct($sessionRepository, $sessionApi, $retryHandler, $configuration, $logger);
                $this->calls = &$calls;
            }

            protected function loadCustomer(int $customerId): \Customer
            {
                $customer = new \Customer();
                $customer->id = $customerId;
                $customer->firstname = 'Registered';

                return $customer;
            }

            protected function createGuestCustomer(array $fields, \Cart $cart): \Customer
            {
                $this->calls['guests'][] = $fields;
                $customer = new \Customer();
                $customer->id = 900;
                $customer->firstname = $fields['firstname'];

                return $customer;
            }

            protected function isReusableAddress(int $addressId, int $customerId): bool
            {
                return in_array($addressId, $this->calls['reusable'], true);
            }

            protected function createAddress(array $fields, int $customerId, int $cartId): int
            {
                $this->calls['addresses'][] = $fields;

                return 500 + count($this->calls['addresses']);
            }

            protected function findCarrierIdByReference(string $reference): int
            {
                return $this->calls['carriers'][$reference] ?? 0;
            }

            protected function refreshContext(\Cart $cart, \Customer $customer): void
            {
                $this->calls['customer'] = $customer;
            }
        };
    }
}
