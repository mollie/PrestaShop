<?php

namespace Mollie\Action;

use Exception;
use Mollie\DTO\UpdateOrderTotalsData;
use Mollie\Exception\CouldNotUpdateOrderTotals;
use Mollie\Repository\OrderRepositoryInterface;
use Mollie\Utility\NumberUtility;
use Order;

class UpdateOrderTotalsAction
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @throws CouldNotUpdateOrderTotals
     */
    public function run(UpdateOrderTotalsData $updateOrderTotalsData): void
    {
        try {
            /** @var Order $order */
            $order = $this->orderRepository->findOneBy([
                'id_order' => $updateOrderTotalsData->getOrderId(),
            ]);

            $order->total_paid_tax_excl = NumberUtility::toPrecision(
                NumberUtility::plus(
                    $updateOrderTotalsData->getOriginalCartAmountTaxExcl(),
                    $updateOrderTotalsData->getPaymentFeeTaxExcl()
                )
            );

            $order->total_paid_tax_incl = NumberUtility::toPrecision(
                NumberUtility::plus(
                    $updateOrderTotalsData->getOriginalCartAmountTaxIncl(),
                    $updateOrderTotalsData->getPaymentFeeTaxIncl()
                )
            );

            $order->total_paid = $updateOrderTotalsData->getTransactionAmount();

            $order->update();
        } catch (Exception $exception) {
            throw CouldNotUpdateOrderTotals::failedToUpdateOrderTotals($exception);
        }
    }
}
