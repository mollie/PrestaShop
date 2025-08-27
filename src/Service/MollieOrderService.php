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
use Mollie\Repository\ProductRepository;
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

    public function assignShippingStatus(array $products, string $mollieTransactionId)
    {
        if (!TransactionUtility::isOrderTransaction($mollieTransactionId)) {
            return $products;
        }

        $mollieOrder = $this->mollie->getApiClient()->orders->get($mollieTransactionId, ['embed' => 'payments']);
        $shipments = $mollieOrder->shipments();

        foreach ($products as &$product) {
            $product['isShipped'] = false;

            foreach ($shipments as $shipment) {
                foreach ($shipment->lines as $shipmentLine) {
                    if ($shipmentLine->metadata->idProduct === $product['id']) {
                        $product['isShipped'] = true;
                        break 2;
                    }
                }
            }
        }
        unset($product);

        return $products;
    }

    public function assignRefundStatus(array $products, string $mollieTransactionId)
    {
        $mollieOrder = TransactionUtility::isOrderTransaction($mollieTransactionId)
            ? $this->mollie->getApiClient()->orders->get($mollieTransactionId, ['embed' => 'refunds'])
            : $this->mollie->getApiClient()->payments->get($mollieTransactionId, ['embed' => 'refunds']);

        $refunds = $mollieOrder->refunds();

        foreach ($products as &$product) {
            $product['isRefunded'] = false;

            foreach ($refunds as $refund) {
                if (!$refund->lines) {
                    continue;
                }

                foreach ($refund->lines as $refundLine) {
                    if ($refundLine->metadata->idProduct === $product['id']) {
                        $product['isRefunded'] = true;
                        break 2;
                    }
                }
            }
        }
        unset($product);

        return $products;
    }

    public function assignCaptureStatus(array $products, string $mollieTransactionId)
    {
        if (!TransactionUtility::isOrderTransaction($mollieTransactionId)) {
            return $products;
        }

        $mollieOrder = $this->mollie->getApiClient()->orders->get($mollieTransactionId, ['embed' => 'payments']);
        $payments = $mollieOrder->payments();

        foreach ($products as &$product) {
            $product['isCaptured'] = false;

            foreach ($payments as $payment) {
                if (isset($payment->captures)) {
                    foreach ($payment->captures as $capture) {
                        if (isset($capture->metadata->idProduct) && $capture->metadata->idProduct === $product['id']) {
                            $product['isCaptured'] = true;
                            break 2;
                        }
                    }
                }
            }
        }
        unset($product);

        return $products;
    }


}
