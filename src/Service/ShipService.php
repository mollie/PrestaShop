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

use Mollie;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Order as MollieOrderAlias;
use Mollie\Utility\ShipUtility;
use Mollie\Utility\TransactionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ShipService
{
    const FILE_NAME = 'ShipService';

    /**
     * @var Mollie
     */
    private $module;

    public function __construct(Mollie $module)
    {
        $this->module = $module;
    }

    /**
     * @param string $transactionId
     * @param array $lines
     * @param array|null $tracking
     *
     * @return array
     *
     * @since 3.3.0
     */
    public function doShipOrderLines($transactionId, $lines = [], $tracking = null)
    {
        try {
            /** @var MollieOrderAlias $payment */
            $order = $this->module->getApiClient()->orders->get($transactionId, ['embed' => 'payments']);
            $lines = $lines ?: $order->lines;
            $shipmentData = [];

            if (!empty($lines)) {
                $shipmentData['lines'] = ShipUtility::getShipLines($lines, $order->lines);
            }

            if ($tracking && !empty($tracking['carrier']) && !empty($tracking['code'])) {
                $shipmentData['tracking'] = $tracking;
            }

            $order->createShipment($shipmentData);
        } catch (ApiException $e) {
            return [
                'success' => false,
                'message' => $this->module->l('The product(s) could not be shipped!', self::FILE_NAME),
                'detailed' => $e->getMessage(),
            ];
        }

        return [
            'success' => true,
            'message' => '',
            'detailed' => '',
        ];
    }

    public function isShipped(string $transactionId): bool
    {
        if (!TransactionUtility::isOrderTransaction($transactionId)) {
            return false;
        }

        $order = $this->module->getApiClient()->orders->get($transactionId, ['embed' => 'payments']);

        return $order->shipments()->count() > 0;
    }
}
