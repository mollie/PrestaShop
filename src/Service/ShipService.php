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
use Mollie\Logger\LoggerInterface;
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

    public function isShipped(array $products): bool
    {
        foreach ($products as $product) {
            if (empty($product['isShipped']) && $product['name'] != 'Shipping' && $product['name'] != 'Discount') {
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
