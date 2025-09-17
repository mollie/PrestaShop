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

if (!defined('_PS_VERSION_')) {
    exit;
}

class CancelService
{
    const FILE_NAME = 'CancelService';

    private $module;
    private $logger;

    public function __construct(Mollie $module)
    {
        $this->module = $module;
        $this->logger = $this->module->getService(LoggerInterface::class);
    }

    public function handleCancel($transactionId, $orderlineId = null)
    {
        try {
            $order = $this->module->getApiClient()->orders->get($transactionId, ['embed' => 'payments']);

            if ($orderlineId) {
                $order->cancelLines(['lines' => [['id' => $orderlineId]]]);
                $message = $this->module->l('Order line has been canceled successfully.');
            } else {
                $order->cancel();
                $message = $this->module->l('Order has been canceled successfully.');
            }

            return [
                'success' => true,
                'message' => $message,
            ];
        } catch (ApiException $e) {
            return $this->createErrorResponse(
                $this->module->l('The order could not be canceled!'),
                $e
            );
        }
    }

    public function isCanceled(string $transactionId): bool
    {
        try {
            $order = $this->module->getApiClient()->orders->get($transactionId, ['embed' => 'payments']);

            return $order->status === 'canceled';
        } catch (ApiException $e) {
            $this->logger->error(sprintf('%s - Error while checking cancel status.', self::FILE_NAME), [
                'exceptions' => ExceptionUtility::getExceptions($e),
            ]);

            return false;
        }
    }

    private function createErrorResponse(string $message, ?\Throwable $e = null): array
    {
        $this->logger->error(sprintf('%s - Error while processing the cancel.', self::FILE_NAME), [
            'exceptions' => ExceptionUtility::getExceptions($e),
        ]);

        return [
            'success' => false,
            'message' => $message,
            'detailed' => $e ? $e->getMessage() : '',
        ];
    }
}
