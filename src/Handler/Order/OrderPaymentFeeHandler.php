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
 *
 * @category   Mollie
 *
 * @see       https://www.mollie.nl
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Handler\Order;

use Cart;
use Exception;
use Mollie\Action\CreateOrderPaymentFeeAction;
use Mollie\Action\UpdateOrderTotalsAction;
use Mollie\Api\Resources\Order as MollieOrderAlias;
use Mollie\Api\Resources\Payment as MolliePaymentAlias;
use Mollie\DTO\CreateOrderPaymentFeeActionData;
use Mollie\DTO\UpdateOrderTotalsData;
use Mollie\Exception\CouldNotCreateOrderPaymentFee;
use Mollie\Exception\CouldNotUpdateOrderTotals;
use Mollie\Exception\FailedToProvidePaymentFeeException;
use Mollie\Exception\OrderCreationException;
use Mollie\Handler\Exception\CouldNotHandleOrderPaymentFee;
use Mollie\Provider\PaymentFeeProviderInterface;
use Mollie\Repository\CartRepositoryInterface;
use Mollie\Repository\OrderRepositoryInterface;
use Mollie\Service\PaymentMethodService;
use Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderPaymentFeeHandler
{
    /** @var PaymentMethodService */
    private $paymentMethodService;
    /** @var PaymentFeeProviderInterface */
    private $paymentFeeProvider;
    /** @var CreateOrderPaymentFeeAction */
    private $createOrderPaymentFeeAction;
    /** @var UpdateOrderTotalsAction */
    private $updateOrderTotalsAction;
    /** @var OrderRepositoryInterface */
    private $orderRepository;
    /** @var CartRepositoryInterface */
    private $cartRepository;

    public function __construct(
        PaymentMethodService $paymentMethodService,
        PaymentFeeProviderInterface $paymentFeeProvider,
        CreateOrderPaymentFeeAction $createOrderPaymentFeeAction,
        UpdateOrderTotalsAction $updateOrderTotalsAction,
        OrderRepositoryInterface $orderRepository,
        CartRepositoryInterface $cartRepository
    ) {
        $this->paymentMethodService = $paymentMethodService;
        $this->paymentFeeProvider = $paymentFeeProvider;
        $this->createOrderPaymentFeeAction = $createOrderPaymentFeeAction;
        $this->updateOrderTotalsAction = $updateOrderTotalsAction;
        $this->orderRepository = $orderRepository;
        $this->cartRepository = $cartRepository;
    }

    /**
     * @param MollieOrderAlias|MolliePaymentAlias $apiPayment
     *
     * @throws CouldNotHandleOrderPaymentFee
     */
    public function addOrderPaymentFee(int $orderId, $apiPayment): void
    {
        /** @var Order $order */
        $order = $this->orderRepository->findOneBy([
            'id_order' => $orderId,
        ]);

        /** @var Cart $cart */
        $cart = $this->cartRepository->findOneBy([
            'id_cart' => $order->id_cart,
        ]);

        try {
            $originalAmountWithTax = (float) $cart->getOrderTotal(
                true,
                Cart::BOTH
            );

            $originalAmountWithoutTax = (float) $cart->getOrderTotal(
                false,
                Cart::BOTH
            );
        } catch (Exception $exception) {
            throw CouldNotHandleOrderPaymentFee::unknownError($exception);
        }

        try {
            $paymentMethod = $this->paymentMethodService->getPaymentMethod($apiPayment);
        } catch (OrderCreationException $exception) {
            throw CouldNotHandleOrderPaymentFee::failedToRetrievePaymentMethod($exception);
        }

        try {
            $paymentFeeData = $this->paymentFeeProvider->getPaymentFee($paymentMethod, (float) $originalAmountWithTax);
        } catch (FailedToProvidePaymentFeeException $exception) {
            throw CouldNotHandleOrderPaymentFee::failedToRetrievePaymentFee($exception);
        }

        try {
            $this->createOrderPaymentFeeAction->run(CreateOrderPaymentFeeActionData::create(
                $orderId,
                (int) $order->id_cart,
                $paymentFeeData->getPaymentFeeTaxIncl(),
                $paymentFeeData->getPaymentFeeTaxExcl()
            ));
        } catch (CouldNotCreateOrderPaymentFee $exception) {
            throw CouldNotHandleOrderPaymentFee::failedToCreateOrderPaymentFee($exception);
        }

        try {
            $this->updateOrderTotalsAction->run(UpdateOrderTotalsData::create(
                $orderId,
                $paymentFeeData->getPaymentFeeTaxIncl(),
                $paymentFeeData->getPaymentFeeTaxExcl(),
                // TODO abstraction for apiPayment
                (float) $apiPayment->amount->value,
                $originalAmountWithTax,
                $originalAmountWithoutTax
            ));
        } catch (CouldNotUpdateOrderTotals $exception) {
            throw CouldNotHandleOrderPaymentFee::failedToUpdateOrderTotalWithPaymentFee($exception);
        }
    }
}
