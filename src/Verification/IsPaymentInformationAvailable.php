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

namespace Mollie\Verification;

use Mollie\Repository\PaymentMethodRepositoryInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class IsPaymentInformationAvailable
{
    /** @var PaymentMethodRepositoryInterface */
    private $paymentMethodRepository;

    public function __construct(PaymentMethodRepositoryInterface $paymentMethodRepository)
    {
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    public function verify(int $orderId): bool
    {
        return $this->hasPaymentInformation($orderId);
    }

    private function hasPaymentInformation(int $orderId): bool
    {
        $payment = $this->paymentMethodRepository->getPaymentBy('order_id', (int) $orderId);

        return !(empty($payment) || empty($payment['transaction_id']));
    }
}
