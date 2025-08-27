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
        $refunds = $mollieOrder->refunds();

        foreach ($products as $product) {
            foreach ($refunds as $refund) {
                if ($refund->metadata->idProduct === $product['id_product']) {
                    $product['isRefunded'] = true;
                }
            }
        }

        return $products;
    }
}
