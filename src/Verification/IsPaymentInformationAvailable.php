<?php

namespace Mollie\Verification;

use Mollie\Repository\PaymentMethodRepositoryInterface;

class IsPaymentInformationAvailable
{
    /** @var PaymentMethodRepositoryInterface */
    private $paymentMethodRepository;

    public function __construct(PaymentMethodRepositoryInterface $paymentMethodRepository)
    {
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    /**
     * @param int $orderId
     *
     * @return bool
     */
    public function verify(int $orderId)
    {
        return $this->hasPaymentInformation($orderId);
    }

    /**
     * @param int $orderId
     *
     * @return bool
     */
    private function hasPaymentInformation(int $orderId)
    {
        $payment = $this->paymentMethodRepository->getPaymentBy('order_id', (int) $orderId);

        return !(empty($payment) || empty($payment['transaction_id']));
    }
}
