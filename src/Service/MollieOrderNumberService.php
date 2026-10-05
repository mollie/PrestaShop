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

namespace Mollie\Service;

use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Order as MollieOrder;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MollieOrderNumberService
{
    /**
     * Order::update() also resends both addresses, and Klarna or Billie can refuse
     * that after authorisation, so only the order number is sent.
     */
    public function assign(MollieApiClient $client, MollieOrder $order, string $orderNumber): void
    {
        $order->orderNumber = $orderNumber;

        foreach ($order->payments() ?: [] as $payment) {
            $payment->description = 'Order ' . $orderNumber;
            $payment->update();
        }

        $client->orders->update($order->id, ['orderNumber' => $orderNumber]);
    }
}
