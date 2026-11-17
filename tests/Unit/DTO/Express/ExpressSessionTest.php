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

namespace Mollie\Tests\Unit\DTO\Express;

use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Session;
use Mollie\DTO\Express\ExpressSession;
use Mollie\DTO\Express\ExpressSessionRequest;
use Mollie\Tests\Unit\BaseTestCase;

class ExpressSessionTest extends BaseTestCase
{
    public function testItReadsTheFieldsTheModuleNeedsFromTheResource(): void
    {
        $resource = new Session($this->mock(MollieApiClient::class));
        $resource->id = 'sess_123';
        $resource->clientAccessToken = 'token';
        $resource->status = 'open';
        $resource->amount = (object) ['currency' => 'EUR', 'value' => '59.90'];
        $resource->createdAt = '2026-10-05T10:00:00+00:00';

        $session = ExpressSession::fromResource($resource);

        $this->assertSame('sess_123', $session->getId());
        $this->assertSame('token', $session->getClientAccessToken());
        $this->assertSame('59.90', $session->getAmountValue());
        $this->assertSame('EUR', $session->getAmountCurrency());
        $this->assertSame('2026-10-05T10:00:00+00:00', $session->getCreatedAt());
        $this->assertTrue($session->isOpen());
        $this->assertFalse($session->isCompleted());
    }

    public function testItReadsTheDescriptionThePaymentWillCarry(): void
    {
        $resource = new Session($this->mock(MollieApiClient::class));
        $resource->id = 'sess_123';
        $resource->status = 'open';
        $resource->description = 'mol_42abc';

        $this->assertSame('mol_42abc', ExpressSession::fromResource($resource)->getDescription());
        $this->assertSame('', (new ExpressSession('sess_1', '', 'open', '1.00', 'EUR', ''))->getDescription());
    }

    public function testItToleratesAResourceWithoutOptionalFields(): void
    {
        $resource = new Session($this->mock(MollieApiClient::class));
        $resource->id = 'sess_123';
        $resource->status = 'completed';

        $session = ExpressSession::fromResource($resource);

        $this->assertSame('', $session->getClientAccessToken());
        $this->assertSame('', $session->getAmountValue());
        $this->assertTrue($session->isCompleted());
        $this->assertFalse($session->isOpen());
    }

    /**
     * @dataProvider openStatusProvider
     */
    public function testOnlyPayableStatusesCountAsOpen(string $status, bool $expected): void
    {
        $session = new ExpressSession('sess_1', '', $status, '1.00', 'EUR', '');

        $this->assertSame($expected, $session->isOpen());
    }

    public function openStatusProvider(): array
    {
        return [
            'open' => ['open', true],
            'created' => ['created', true],
            'ready_for_processing' => ['ready_for_processing', true],
            'completed' => ['completed', false],
            'expired' => ['expired', false],
            'failed' => ['failed', false],
        ];
    }

    public function testRequestSerializesToTheSessionsApiBody(): void
    {
        $request = new ExpressSessionRequest(
            'EUR',
            '59.90',
            'mol_abc',
            [['description' => 'T-shirt']],
            'https://shop.test/return',
            'https://shop.test/webhook',
            ['cart_id' => 7]
        );

        $this->assertSame([
            'amount' => ['currency' => 'EUR', 'value' => '59.90'],
            'description' => 'mol_abc',
            'lines' => [['description' => 'T-shirt']],
            'redirectUrl' => 'https://shop.test/return',
            'metadata' => ['cart_id' => 7],
            'payment' => ['webhookUrl' => 'https://shop.test/webhook'],
            'requiredCustomerDetails' => [],
        ], $request->jsonSerialize());
    }

    public function testRequestSerializesTheShippingCallbackOnlyWhenSet(): void
    {
        $request = new ExpressSessionRequest(
            'EUR',
            '54.90',
            'mol_abc',
            [],
            'https://shop.test/return',
            'https://shop.test/webhook',
            [],
            ['email', 'billing-address', 'shipping-address'],
            ['callbackUrl' => 'https://shop.test/shipping']
        );

        $body = $request->jsonSerialize();

        $this->assertSame(['callbackUrl' => 'https://shop.test/shipping'], $body['shipping']);
        $this->assertSame(['callbackUrl' => 'https://shop.test/shipping'], $request->getShipping());
        $this->assertSame(['email', 'billing-address', 'shipping-address'], $body['requiredCustomerDetails']);

        $withoutShipping = new ExpressSessionRequest('EUR', '54.90', 'mol_abc', [], 'r', 'w', [], ['email']);

        $this->assertArrayNotHasKey('shipping', $withoutShipping->jsonSerialize());
        $this->assertSame([], $withoutShipping->getShipping());
    }

    public function testItReadsTheCollectedAddressesAndTheChosenShippingOption(): void
    {
        $address = [
            'givenName' => 'Jane',
            'familyName' => 'Customer',
            'email' => 'jane.customer@example.test',
            'streetAndNumber' => 'Teststraat 1',
            'city' => 'Amsterdam',
            'region' => 'Noord-Holland',
            'postalCode' => '1000 AA',
            'country' => 'NL',
        ];

        $resource = new Session($this->mock(MollieApiClient::class));
        $resource->id = 'sess_123';
        $resource->status = 'completed';
        $resource->billingAddress = (object) $address;
        $resource->shippingAddress = (object) array_merge($address, ['city' => 'Utrecht']);
        $resource->lines = [
            (object) ['type' => 'physical', 'description' => 'T-shirt', 'sku' => 'tshirt'],
            (object) ['type' => 'shipping_fee', 'description' => 'Courier', 'reference' => '12', 'sku' => 'carrier_sku'],
        ];

        $session = ExpressSession::fromResource($resource);

        $this->assertSame($address, $session->getBillingAddress());
        $this->assertSame('Utrecht', $session->getShippingAddress()['city']);
        $this->assertSame('12', $session->getShippingReference());
    }

    public function testTheShippingReferenceFallsBackToTheSku(): void
    {
        $resource = new Session($this->mock(MollieApiClient::class));
        $resource->id = 'sess_123';
        $resource->status = 'completed';
        $resource->lines = [(object) ['type' => 'shipping_fee', 'sku' => '15']];

        $this->assertSame('15', ExpressSession::fromResource($resource)->getShippingReference());
    }

    public function testMissingAddressesAndShippingGiveNull(): void
    {
        $resource = new Session($this->mock(MollieApiClient::class));
        $resource->id = 'sess_123';
        $resource->status = 'open';
        $resource->lines = [(object) ['type' => 'physical', 'sku' => 'tshirt']];

        $session = ExpressSession::fromResource($resource);

        $this->assertNull($session->getBillingAddress());
        $this->assertNull($session->getShippingAddress());
        $this->assertNull($session->getShippingReference());

        $bare = new ExpressSession('sess_1', '', 'open', '1.00', 'EUR', '');

        $this->assertNull($bare->getBillingAddress());
        $this->assertNull($bare->getShippingReference());
    }
}
