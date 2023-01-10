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
use Mollie\Api\Resources\Order;
use PrestaShopDatabaseException;
use PrestaShopException;

class CancelService
{
    const FILE_NAME = 'CancelService';
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
     *
     * @return array
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 3.3.0
     */
    public function doCancelOrderLines($transactionId, $lines = [])
    {
        try {
            /** @var Order $payment */
            $order = $this->module->getApiClient()->orders->get($transactionId, ['embed' => 'payments']);
            if ([] === $lines) {
                $order->cancel();
            } else {
                $cancelableLines = [];
                foreach ($lines as $line) {
                    $cancelableLines[] = ['id' => $line['id'], 'quantity' => $line['quantity']];
                }
                $order->cancelLines(['lines' => $cancelableLines]);
            }
        } catch (ApiException $e) {
            return [
                'success' => false,
                'message' => $this->module->l('The product(s) could not be canceled!', self::FILE_NAME),
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
