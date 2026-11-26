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
use Mollie\Api\Types\PaymentStatus;
use Mollie\Exception\MollieException;
use Mollie\Utility\NumberUtility;
use Mollie\Utility\TransactionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MollieOrderService
{
    const FILE_NAME = 'MollieOrderService';

    /** @var Mollie */
    private $module;

    public function __construct(Mollie $module)
    {
        $this->module = $module;
    }

    public function getRefundableAmount(string $mollieTransactionId): float
    {
        $mollieOrder = TransactionUtility::isOrderTransaction($mollieTransactionId)
            ? $this->module->getApiClient()->orders->get($mollieTransactionId, ['embed' => 'payments'])
            : $this->module->getApiClient()->payments->get($mollieTransactionId, ['embed' => 'payments']);

        if (TransactionUtility::isOrderTransaction($mollieOrder->id)) {
            $amountRefunded = 0;
            foreach ($mollieOrder->lines as $line) {
                $amountRefunded += $line->amountRefunded->value;
            }

            return NumberUtility::minus($mollieOrder->amount->value, $amountRefunded);
        }

        if (isset($mollieOrder->amountRemaining)) {
            return (float) $mollieOrder->amountRemaining->value;
        }

        // Mollie can leave out amountRemaining on an authorized payment, and only the captured part can be refunded
        if ($mollieOrder->status === PaymentStatus::STATUS_AUTHORIZED) {
            $captured = isset($mollieOrder->amountCaptured) ? (float) $mollieOrder->amountCaptured->value : 0.0;
            $refunded = isset($mollieOrder->amountRefunded) ? (float) $mollieOrder->amountRefunded->value : 0.0;

            return max(0.0, NumberUtility::minus($captured, $refunded));
        }

        if (isset($mollieOrder->amount)) {
            return (float) $mollieOrder->amount->value;
        }

        throw new MollieException('Invalid payment type');
    }
}
