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
use Mollie\Logger\LoggerInterface;
use Mollie\Utility\TransactionUtility;
use Validate;

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
     * @param string|null $orderlineId
     * @param array|null $tracking
     *
     * @return array
     *
     * @since 3.3.0
     */
    public function handleShip($transactionId, $orderlineId = null, $tracking = null)
    {
        try {
            /** @var MollieOrderAlias $payment */
            $order = $this->module->getApiClient()->orders->get($transactionId, ['embed' => 'payments']);
            $shipmentData = [];

            if ($orderlineId) {
                $shipmentData['lines'] = [
                    [
                        'id' => $orderlineId,
                    ],
                ];
            }

            if ($tracking['carrier'] && $tracking['code'] && $tracking['tracking_url']) {
                $validationResult = $this->validateTracking($tracking);

                if (!$validationResult['success']) {
                    return $validationResult;
                }

                $shipmentData['tracking'] = [
                    'carrier' => $tracking['carrier'],
                    'code' => $tracking['code'],
                    'url' => $tracking['tracking_url'],
                ];
            }

            $order->createShipment($shipmentData);
        } catch (ApiException $e) {
            /** @var LoggerInterface $logger */
            $logger = $this->module->getService(LoggerInterface::class);

            $logger->error(sprintf('%s - Failed to ship order lines', self::FILE_NAME), [
                'error_message' => $e->getMessage(),
            ]);

            if (strpos($e->getMessage(), 'exceeds the amount') !== false) {
                return [
                    'success' => false,
                    'message' => $this->module->l('The product(s) could not be shipped! The amount exceeds the order amount. Use "Ship All".', self::FILE_NAME),
                    'detailed' => $e->getMessage(),
                ];
            }

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

        $products = $this->module->getApiClient()->orders->get($transactionId, ['embed' => 'payments'])->lines;

        foreach ($products as $product) {
            if ($product->quantity != $product->quantityShipped) {
                return false;
            }
        }

        return true;
    }

    private function validateTracking(array $tracking): array
    {
        if (!Validate::isAbsoluteUrl($tracking['tracking_url'])) {
            return [
                'success' => false,
                'message' => $this->module->l('Invalid tracking URL provided', self::FILE_NAME),
            ];
        }

        if (!Validate::isString($tracking['carrier']) || !Validate::isString($tracking['code'])) {
            return [
                'success' => false,
                'message' => $this->module->l('Invalid tracking data provided', self::FILE_NAME),
            ];
        }

        return [
            'success' => true,
            'message' => '',
        ];
    }
}
