<?php

namespace Mollie\Service;

use _PhpScoper5eddef0da618a\Mollie\Api\Exceptions\ApiException;
use Context;
use Mollie;
use Mollie\Exception\CancelPendingOrderException;
use Mollie\Repository\PendingOrderCartRepository;
use Mollie\Utility\TransactionUtility;
use Order;

/**
 * @todo: seems like cancel does not work when payment is in open state or payment becomes paid for instance.
 * Is it possible to cancel payment somehow?
 */
class CancelPendingOrderService
{
    private $cancelService;
    private $pendingOrderCartRepository;
    private $module;

    public function __construct(
        CancelService $cancelService,
        PendingOrderCartRepository $pendingOrderCartRepository,
        Mollie $module
    ) {
        $this->cancelService = $cancelService;
        $this->pendingOrderCartRepository = $pendingOrderCartRepository;
        $this->module = $module;
    }

    /**
     * @param string $transactionId
     * @param Order $order
     *
     * @return bool
     *
     * @throws CancelPendingOrderException
     */
    public function cancel($transactionId)
    {
        $globalContext = Context::getContext();
        $cart = $globalContext->cart;

        if (!$this->pendingOrderCartRepository->hasPendingCancellableOrder($cart->id)) {
            return false;
        }

        $isOrderTransaction = TransactionUtility::isOrderTransaction($transactionId);

        if ($isOrderTransaction) {
            $this->cancelOrder($transactionId);
        }

        $this->cancelPayment($transactionId);

        return true;
    }

    /**
     * @param $transactionId
     * @param Order $cart
     * @throws CancelPendingOrderException
     */
    private function cancelOrder($transactionId)
    {
        $status = $this->cancelService->doCancelOrderLines($transactionId);

        if (!$status['success']) {
            throw new CancelPendingOrderException(
                "Transaction {$transactionId} cant be cancelled due to previous error: {$status['detailed']}"
            );
        }
    }

    /**
     * @param $transactionId
     * @param Order $order
     *
     * @throws CancelPendingOrderException
     */
    private function cancelPayment($transactionId)
    {
        $cancelledPayment = null;
        try {
            $cancelledPayment = $this->module->api->payments->delete($transactionId);
        } catch (ApiException $e) {
            throw new CancelPendingOrderException(
                "Transaction {$transactionId} payment cant be cancelled due to error: {$e->getMessage()}, code: {$e->getCode()}, field: {$e->getField()}",
                $e->getCode()
            );
        }

        if (null === $cancelledPayment) {
            throw new CancelPendingOrderException(
                "Transaction {$transactionId} payment cannot be cancelled."
            );
        }
    }
}