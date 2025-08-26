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
use Mollie\Logger\LoggerInterface;
use Mollie\Utility\ExceptionUtility;
use Mollie\Utility\TransactionUtility;
use Product;
use Throwable;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MollieOrderService
{
    const FILE_NAME = 'MollieOrderService';

    /** @var Mollie $mollie */
    private $mollie;

    /** @var LoggerInterface $logger */
    private $logger;

    public function __construct(Mollie $mollie, LoggerInterface $logger)
    {
        $this->mollie = $mollie;
        $this->logger = $logger;
    }

    public function getStatusesByTransactionId(string $transactionId): array
    {
        try {
            $isOrderTransaction = TransactionUtility::isOrderTransaction($transactionId);
            $orderInfo = ['lines' => []];

            if ($isOrderTransaction) {
                $mollieOrder = $this->mollie->getApiClient()->orders->get($transactionId, ['embed' => 'payments']);
                $shipments = $mollieOrder->shipments();
                $refunds = $mollieOrder->refunds();

                foreach ($mollieOrder->lines as $line) {
                    $isShipped = false;
                    $isRefunded = false;

                    foreach ($shipments as $shipment) {
                        if ($shipment->lineId === $line->id) {
                            $isShipped = true;
                            break;
                        }
                    }

                    foreach ($refunds as $refund) {
                        if ($refund->lineId === $line->id) {
                            $isRefunded = true;
                            break;
                        }
                    }

                    $orderInfo['lines'][] = [
                        'id' => $line->id,
                        'name' => $line->name,
                        'sku' => $line->sku,
                        'isShipped' => $isShipped,
                        'isRefunded' => $isRefunded,
                        'isCaptured' => false,
                    ];
                }
            } else {
                $molliePayment = $this->mollie->getApiClient()->payments->get($transactionId);
                $captures = $molliePayment->captures();
                $refunds = $molliePayment->refunds();

                foreach ($molliePayment->lines as $line) {
                    $isCaptured = false;
                    $isRefunded = false;

                    foreach ($captures as $capture) {
                        if ($capture->name === $line->name) {
                            $isCaptured = true;
                            break;
                        }
                    }

                    foreach ($refunds as $refund) {
                        if ($refund->name === $line->name) {
                            $isRefunded = true;
                            break;
                        }
                    }

                    $orderInfo['lines'][] = [
                        'id' => $line->id,
                        'name' => $line->name,
                        'sku' => $line->sku,
                        'isShipped' => false,
                        'isRefunded' => $isRefunded,
                        'isCaptured' => $isCaptured,
                    ];
                }
            }

            return $orderInfo;
        } catch (ApiException $e) {
            $this->logger->error(sprintf('%s - Failed to retrieve order info: %s', self::FILE_NAME, $e->getMessage()), [
                'exceptions' => ExceptionUtility::getExceptions($e),
            ]);

            return [];
        } catch (Throwable $e) {
            $this->logger->error(sprintf('%s - Failed to retrieve order info: %s', self::FILE_NAME, $e->getMessage()), [
                'exceptions' => ExceptionUtility::getExceptions($e),
            ]);

            return [];
        }
    }

    public function mergeOrderStatusesWithProducts(array $products, string $transactionId): array
    {
        $mollieOrderStatuses = $this->getStatusesByTransactionId($transactionId);
        $mollieStatusesMap = [];

        foreach ($mollieOrderStatuses['lines'] as $mollieOrderStatus) {
            $mollieStatusesMap[$mollieOrderStatus['id']] = $mollieOrderStatus;
        }

        return array_map(function($product) use ($mollieStatusesMap) {
            $productData = [
                'id' => $product['id_order_detail'],
                'name' => $product['product_name'],
                'price' => $product['total_price_tax_incl'],
                'quantity' => $product['product_quantity'],
                'isShipped' => false,
                'isRefunded' => false,
                'isCaptured' => false,
            ];

            if (isset($mollieStatusesMap[$product['id_order_detail']])) {
                $mollieStatus = $mollieStatusesMap[$product['id_order_detail']];
                $productData['isShipped'] = $mollieStatus['isShipped'];
                $productData['isRefunded'] = $mollieStatus['isRefunded'];
                $productData['isCaptured'] = $mollieStatus['isCaptured'];
            }

            return $productData;
        }, $products);
    }
}
