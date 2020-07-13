<?php

namespace Mollie\Service;

use _PhpScoper5eddef0da618a\Mollie\Api\MollieApiClient;
use _PhpScoper5eddef0da618a\Mollie\Api\Resources\Payment;
use _PhpScoper5eddef0da618a\Mollie\Api\Types\PaymentStatus;
use Cart;
use Context;
use Customer;
use Mail;
use Mollie;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Utility\EnvironmentUtility;
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
            $response = $this->sendSecondChanceMailWithOrderAPI($api, $transactionId, $payment['method']);
        } else {
            $response = $this->sendSecondChanceMailWithPaymentApi($api, $transactionId);
        }

        if ($response['success']) {
            $this->mailService->sendSecondChanceMail($customer, $response['checkoutUrl'], $payment['method']);
        }

        return $response;
    }

    public function sendSecondChanceMailWithOrderAPI(MollieApiClient $api, $transactionId, $paymentMethod)
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

    public function sendSecondChanceMailWithPaymentApi(MollieApiClient $api, $transactionId)
    {
        $context = Context::getContext();
        $paymentApi = $api->payments->get($transactionId);

        if ($paymentApi->isPaid() || $paymentApi->isAuthorized()) {
            return
                [
                    'success' => false,
                    'message' =>
                        $this->module->l('Failed to send second chance email! Order is already paid or expired!')
                ];
        }

        if (null !== $paymentApi->getCheckoutUrl()) {
            $checkoutUrl = $paymentApi->getCheckoutUrl();

            return [
            'success' => true,
            'message' => $this->module->l('Second chance email was successfully send!'),
            'checkoutUrl' => $checkoutUrl
        ];
        }
        $qrCode = false;

        $paymentData = [
            'amount' => [
                'value' => $paymentApi->amount->value,
                'currency' => $paymentApi->amount->currency,
            ],
            'redirectUrl' =>($qrCode
                ? $context->link->getModuleLink(
                    'mollie',
                    'qrcode',
                    ['cart_id' => $paymentApi->metadata->cart_id, 'done' => 1, 'rand' => time()],
                    true
                )
                : $context->link->getModuleLink(
                    'mollie',
                    'return',
                    ['cart_id' => $paymentApi->metadata->cart_id, 'utm_nooverride' => 1, 'rand' => time()],
                    true
                )
            ),
            'description' => $paymentApi->description,
            'metadata' => [
                'cart_id' => $paymentApi->metadata->cart_id,
                'order_reference' => $paymentApi->metadata->order_reference,
                'secure_key' => $paymentApi->metadata->secure_key
            ],
        ];

        if (!EnvironmentUtility::isLocalEnvironment()) {
            $paymentData['webhookUrl'] = $context->link->getModuleLink(
                'mollie',
                'webhook',
                [],
                true
            );
        }
        $newPayment = $api->payments->create($paymentData);

        $checkoutUrl = $newPayment->getCheckoutUrl();
//        $molliePayments = $paymentApi->payments();
//        $checkoutUrl = $this->getCheckoutUrl($molliePayments);

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