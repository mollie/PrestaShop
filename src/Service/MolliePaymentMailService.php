<?php
/**
 * Copyright (c) 2012-2020, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @category   Mollie
 * @package    Mollie
 * @link       https://www.mollie.nl
 * @codingStandardsIgnoreStart
 */

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

        $cart = new Cart($paymentApi->metadata->cart_id);
        $customer = new Customer($cart->id_customer);
        $paymentData = [
            'amount' => [
                'value' => $paymentApi->amount->value,
                'currency' => $paymentApi->amount->currency,
            ],
            'redirectUrl' => $context->link->getModuleLink(
                'mollie',
                'return',
                [
                    'cart_id' => $paymentApi->metadata->cart_id,
                    'utm_nooverride' => 1,
                    'rand' => time(),
                    'key' => $customer->secure_key,
                    'customerId' => $customer->id
                ],
                true

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

        if (isset($newPayment)) {
            $updateTransactionId = $this->paymentMethodRepository->updateTransactionId($transactionId, $newPayment->id);

            if ($updateTransactionId) {
                $checkoutUrl = $newPayment->getCheckoutUrl();
                return [
                    'success' => true,
                    'message' => $this->module->l('Second chance email was successfully send!'),
                    'checkoutUrl' => $checkoutUrl
                ];
            }
        }

        return
            [
                'success' => false,
                'message' =>
                    $this->module->l('Failed to send second chance email!')
            ];
    }

    private function getCheckoutUrl($molliePayments)
    {
        $checkoutUrl = '';
        /** @var Payment $molliePayment */
        foreach ($molliePayments as $molliePayment) {
            if ($molliePayment->status === PaymentStatus::STATUS_OPEN ||
                $molliePayment->status === PaymentStatus::STATUS_PENDING
            ) {
                return $molliePayment->getCheckoutUrl();
            }
        }

        return $checkoutUrl;
    }
}
