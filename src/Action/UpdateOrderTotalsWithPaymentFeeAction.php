<?php

namespace Mollie\Action;

use Exception;
use Mollie\DTO\UpdateOrderTotalsWithPaymentFeeData;
use Mollie\Exception\CouldNotUpdateOrderTotalsWithPaymentFee;
use Mollie\Repository\OrderRepositoryInterface;
use Mollie\Utility\NumberUtility;
use Order;

class UpdateOrderTotalsWithPaymentFeeAction
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @throws CouldNotUpdateOrderTotalsWithPaymentFee
     */
    public function run(UpdateOrderTotalsWithPaymentFeeData $updateOrderTotalsWithPaymentFeeData): void
    {
        try {
            /** @var Order $order */
            $order = $this->orderRepository->findOneBy([
                'id_order' => $updateOrderTotalsWithPaymentFeeData->getOrderId(),
            ]);

            $order->total_paid_tax_excl = NumberUtility::toPrecision(
                NumberUtility::plus(
                    $updateOrderTotalsWithPaymentFeeData->getOriginalCartAmountTaxExcl(),
                    $updateOrderTotalsWithPaymentFeeData->getPaymentFeeTaxExcl()
                )
            );

            $order->total_paid_tax_incl = NumberUtility::toPrecision(
                NumberUtility::plus(
                    $updateOrderTotalsWithPaymentFeeData->getOriginalCartAmountTaxIncl(),
                    $updateOrderTotalsWithPaymentFeeData->getPaymentFeeTaxIncl()
                )
            );

            $order->total_paid = $updateOrderTotalsWithPaymentFeeData->getTransactionAmount();
            $order->total_paid_real = $updateOrderTotalsWithPaymentFeeData->getTransactionAmount();

            $order->update();
        } catch (Exception $exception) {
            throw CouldNotUpdateOrderTotalsWithPaymentFee::failedToUpdateOrderTotals($exception);
        }
    }
}
