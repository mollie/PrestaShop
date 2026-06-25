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

declare(strict_types=1);

namespace Mollie\Service;

use Currency;
use MolPaymentMethod;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SegmentOrderStatusHandler
{
    /** @var PaymentMethodRepositoryInterface */
    private $paymentMethodRepository;
    /** @var ConfigurationAdapter */
    private $configuration;
    /** @var SegmentTracker */
    private $segmentTracker;

    public function __construct(
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        ConfigurationAdapter $configuration,
        SegmentTracker $segmentTracker
    ) {
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->configuration = $configuration;
        $this->segmentTracker = $segmentTracker;
    }

    public function trackFirstPaymentCompleted(Order $order): void
    {
        $molliePayment = $this->paymentMethodRepository->getPaymentBy('cart_id', (int) $order->id_cart);

        $methodId = is_array($molliePayment) ? ($molliePayment['method'] ?? '') : '';
        $transactionId = is_array($molliePayment) ? ($molliePayment['transaction_id'] ?? '') : '';
        $apiType = !empty($transactionId) && strpos($transactionId, 'ord_') === 0 ? 'orders' : 'payments';
        $currency = Currency::getCurrencyInstance((int) $order->id_currency)->iso_code ?? '';

        $environment = (int) $this->configuration->get(Config::MOLLIE_ENVIRONMENT);
        $pmId = $this->paymentMethodRepository->getPaymentMethodIdByMethodId($methodId, $environment);
        $methodName = $pmId ? (new MolPaymentMethod((int) $pmId))->method_name ?: $methodId : $methodId;

        $this->segmentTracker->trackFirstPaymentCompleted($methodId, $methodName, $apiType, $currency);
    }
}
