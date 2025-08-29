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
use Mollie\Logger\LoggerInterface;
use Mollie\Adapter\ToolsAdapter;
use Mollie\Utility\TransactionUtility;
use Mollie\Utility\RefundUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MollieOrderService
{
    const FILE_NAME = 'MollieOrderService';

    /** @var Mollie $module */
    private $module;

    /** @var LoggerInterface $logger */
    private $logger;

    public function __construct(Mollie $module, LoggerInterface $logger)
    {
        $this->module = $module;
        $this->logger = $logger;
    }

    public function assignShippingStatus(array $products, string $mollieTransactionId)
    {
        if (!TransactionUtility::isOrderTransaction($mollieTransactionId)) {
            return $products;
        }

        $mollieOrder = $this->module->getApiClient()->orders->get($mollieTransactionId, ['embed' => 'payments']);
        $shipments = $mollieOrder->shipments();

        foreach ($products as &$product) {
            $product['isShipped'] = false;

            foreach ($shipments as $shipment) {
                foreach ($shipment->lines as $shipmentLine) {
                    if (!empty($shipmentLine->metadata) && $shipmentLine->metadata->idProduct === $product['id']) {
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
            ? $this->module->getApiClient()->orders->get($mollieTransactionId, ['embed' => 'refunds'])
            : $this->module->getApiClient()->payments->get($mollieTransactionId, ['embed' => 'refunds']);

        $refunds = $mollieOrder->refunds();

        foreach ($products as &$product) {
            $product['isRefunded'] = false;

            foreach ($refunds as $refund) {
                if (!$refund->lines) {
                    continue;
                }

                foreach ($refund->lines as $refundLine) {
                    if (!empty($refundLine->metadata) && $refundLine->metadata->idProduct === $product['id']) {
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

        $mollieOrder = $this->module->getApiClient()->orders->get($mollieTransactionId, ['embed' => 'payments']);
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

    public function assignDiscounts(array $products, array $discounts)
    {
        /** @var ToolsAdapter $toolsAdapter */
        $toolsAdapter = $this->module->getService(ToolsAdapter::class);

        $result = [];

        foreach ($discounts as $discount) {
            $result[] = [
                'id' => $discount['id_cart_rule'],
                'name' => 'Discount',
                'price_formatted' => $toolsAdapter->displayPrice(-$discount['value']),
                'price' => $discount['value'],
                'quantity' => 1,
                'isRefunded' => false,
                'isShipped' => false,
            ];
        }

        $result = array_merge($products, $result);

        return $result;
    }

    public function getRefundableAmount(string $mollieTransactionId)
    {
        $mollieOrder = TransactionUtility::isOrderTransaction($mollieTransactionId)
            ? $this->module->getApiClient()->orders->get($mollieTransactionId, ['embed' => 'payments'])
            : $this->module->getApiClient()->payments->get($mollieTransactionId, ['embed' => 'payments']);

        if (!TransactionUtility::isOrderTransaction($mollieTransactionId)) {
            if (isset($mollieOrder->amountRefunded)) {
                return $mollieOrder->amount->value - $mollieOrder->amountRefunded->value;
            }

            return $mollieOrder->amount->value;
        } else {
            $refunds = $mollieOrder->refunds();
            $refundedAmount = RefundUtility::getRefundedAmount(iterator_to_array($refunds));

            return $mollieOrder->amount->value - $refundedAmount;
        }
    }
}
