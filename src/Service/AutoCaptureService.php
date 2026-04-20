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

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Logger\LoggerInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AutoCaptureService
{
    const FILE_NAME = 'AutoCaptureService';

    /** @var CaptureService */
    private $captureService;

    /** @var PaymentMethodRepositoryInterface */
    private $paymentMethodRepository;

    /** @var ConfigurationAdapter */
    private $configurationAdapter;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        CaptureService $captureService,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        ConfigurationAdapter $configurationAdapter,
        LoggerInterface $logger
    ) {
        $this->captureService = $captureService;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->configurationAdapter = $configurationAdapter;
        $this->logger = $logger;
    }

    public function handleAutoCaptureOnStatusChange(int $orderId, int $newStatusId): void
    {
        $payment = $this->paymentMethodRepository->getPaymentBy('order_id', $orderId);

        if (!$payment) {
            return;
        }

        $transactionId = $payment['transaction_id'];
        $methodId = $payment['method'] ?? '';

        if (!in_array($methodId, Config::MOLLIE_MANUAL_CAPTURE_ELIGIBLE_METHODS)) {
            return;
        }

        $environment = (int) $this->configurationAdapter->get(Config::MOLLIE_ENVIRONMENT);

        if (!$this->paymentMethodRepository->isManualCapture($methodId, $environment)) {
            return;
        }

        $autoCaptureEnabled = (bool) $this->configurationAdapter->get(
            Config::MOLLIE_METHOD_AUTO_CAPTURE_ENABLED . $methodId
        );

        if (!$autoCaptureEnabled) {
            return;
        }

        $configuredStatuses = json_decode(
            $this->configurationAdapter->get(Config::MOLLIE_METHOD_AUTO_CAPTURE_STATUSES . $methodId) ?: '[]',
            true
        ) ?? [];

        if (!in_array((string) $newStatusId, $configuredStatuses)) {
            return;
        }

        $this->logger->debug(sprintf(
            '%s - Auto-capturing payment %s for order %d on status change to %d',
            self::FILE_NAME,
            $transactionId,
            $orderId,
            $newStatusId
        ));

        $result = $this->captureService->handleCapture($transactionId, null, $orderId);

        if (!$result['success']) {
            $this->logger->error(sprintf(
                '%s - Auto-capture failed for payment %s: %s',
                self::FILE_NAME,
                $transactionId,
                $result['detailed'] ?? $result['message']
            ));
        }
    }
}
