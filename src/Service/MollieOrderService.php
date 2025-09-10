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

use Carrier;
use Mollie;
use Mollie\Logger\LoggerInterface;
use Mollie\Adapter\ToolsAdapter;
use Mollie\Config\Config;
use Mollie\Utility\TransactionUtility;
use Mollie\Utility\NumberUtility;
use Mollie\Exception\MollieException;

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

        if (isset($mollieOrder->amount)) {
            return (float) $mollieOrder->amount->value;
        }

        throw new MollieException('Invalid payment type');
    }
}
