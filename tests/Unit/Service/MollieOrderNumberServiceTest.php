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

namespace Mollie\Tests\Unit\Service;

use Mollie\Api\Endpoints\OrderEndpoint;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Order as MollieOrder;
use Mollie\Service\MollieOrderNumberService;
use PHPUnit\Framework\TestCase;

class MollieOrderNumberServiceTest extends TestCase
{
    public function testSendsOnlyTheOrderNumberAndNoAddresses()
    {
        $orderEndpoint = $this->createMock(OrderEndpoint::class);
        $orderEndpoint->expects($this->once())
            ->method('update')
            ->with('ord_pipres851', ['orderNumber' => 'MOL-42']);

        $client = $this->createMock(MollieApiClient::class);
        $client->orders = $orderEndpoint;

        $order = new MollieOrder($client);
        $order->id = 'ord_pipres851';
        $order->billingAddress = (object) ['streetAndNumber' => 'Keizersgracht 126'];
        $order->shippingAddress = (object) ['streetAndNumber' => 'Keizersgracht 126'];

        (new MollieOrderNumberService())->assign($client, $order, 'MOL-42');

        $this->assertSame('MOL-42', $order->orderNumber);
    }

    public function testOrderWithoutEmbeddedPaymentsIsStillUpdated()
    {
        $orderEndpoint = $this->createMock(OrderEndpoint::class);
        $orderEndpoint->expects($this->once())->method('update');

        $client = $this->createMock(MollieApiClient::class);
        $client->orders = $orderEndpoint;

        $order = new MollieOrder($client);
        $order->id = 'ord_pipres851';

        (new MollieOrderNumberService())->assign($client, $order, 'MOL-42');
    }
}
