<?php

namespace Mollie\Service;

use _PhpScoper5eddef0da618a\Mollie\Api\MollieApiClient;
use _PhpScoper5eddef0da618a\Mollie\Api\Resources\Payment;
use _PhpScoper5eddef0da618a\Mollie\Api\Types\PaymentStatus;
use Cart;
use Customer;
use Mail;
use Mollie;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Utility\TransactionUtility;
use Order;

class MolliePaymentMailService
{

    /**
     * @var PaymentMethodRepository
     */
    private $paymentMethodRepository;
    /**
     * @var Mollie
     */
    private $module;
    /**
     * @var MailService
     */
    private $mailService;

    public function __construct(
        Mollie $module,
        PaymentMethodRepository $paymentMethodRepository,
        MailService $mailService
    ) {
        $this->module = $module;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->mailService = $mailService;
    }

    public function sendSecondChanceMail($orderId)
    {
        $order = new Order($orderId);
        $payment = $this->paymentMethodRepository->getPaymentBy('order_id', $orderId);
        if (!$payment) {
            return false;
        }

        $response = [
            'success' => false,
            'message' => $this->module->l('Failed to created second chance email!')
        ];

        $transactionId = $payment['transaction_id'];

        $customer = new Customer($order->id_customer);

        /** @var MollieApiClient $api */
        $api = $this->module->api;
        if (TransactionUtility::isOrderTransaction($transactionId)) {
            $response = $this->sendSecondChangeMailWithOrderAPI($api, $transactionId, $payment['method']);
        } else {
            $response =
                [
                    'success' => false,
                    'message' => $this->module->l('Failed to send second chance email! Second chance payment can only be created with Order API')
                ];
        }

        if ($response['success']) {
            $this->mailService->sendSecondChanceMail($customer, $response['checkoutUrl'], $payment['method']);
        }

        return $response;
    }

    public function sendSecondChangeMailWithOrderAPI(MollieApiClient $api, $transactionId, $paymentMethod)
    {
        $orderApi = $api->orders->get($transactionId, ['embed' => 'payments']);
        if ($orderApi->isPaid() || $orderApi->isAuthorized() || $orderApi->isShipping() || $orderApi->isCompleted()) {
            return
                [
                    'success' => false,
                    'message' => $this->module->l('Failed to send second chance email! Order is already paid!')
                ];
        }

        $molliePayments = $orderApi->payments();
        $checkoutUrl = $this->getCheckoutUrl($molliePayments);

        if (!$checkoutUrl) {
            /** @var Payment $newPayment */
            $newPayment = $api->orders->get($transactionId)->createPayment(
                [

                ]
            );
            $checkoutUrl = $newPayment->getCheckoutUrl();
        }

        return [
            'success' => true,
            'message' => $this->module->l('Second chance email was successfully send!'),
            'checkoutUrl' => $checkoutUrl
        ];
    }

    private function getCheckoutUrl($molliePayments)
    {
        $checkoutUrl = '';
        /** @var Payment $molliePayment */
        foreach ($molliePayments as $molliePayment) {
            if ($molliePayment->status === PaymentStatus::STATUS_OPEN) {
                $checkoutUrl = $molliePayment->getCheckoutUrl();
            }
        }

        return $checkoutUrl;
    }
}