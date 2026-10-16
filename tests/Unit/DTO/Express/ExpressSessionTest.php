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
}
