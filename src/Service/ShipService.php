<?php

namespace Mollie\Service;

use _PhpScoper5ea00cc67502b\Mollie\Api\Exceptions\ApiException;
use Mollie;
use _PhpScoper5ea00cc67502b\Mollie\Api\Resources\Order as MollieOrderAlias;

class ShipService
{
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
            $order = $this->module->api->orders->get($transactionId, ['embed' => 'payments']);
            $shipment = [
                'lines' => array_map(function ($line) {
                    return array_intersect_key(
                        (array)$line,
                        array_flip([
                            'id',
                            'quantity',
                        ]));
                }, $lines),
            ];
            if ($tracking && !empty($tracking['carrier']) && !empty($tracking['code'])) {
                $shipment['tracking'] = $tracking;
            }
            $order->createShipment($shipment);
        } catch (ApiException $e) {
            return [
                'success' => false,
                'message' => $this->module->l('The product(s) could not be shipped!'),
                'detailed' => $e->getMessage(),
            ];
        }

        return [
            'success' => true,
            'message' => '',
            'detailed' => '',
        ];
    }

}