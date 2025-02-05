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

namespace Mollie\Action;

use Exception;
use Mollie\DTO\UpdateOrderTotalsData;
use Mollie\Exception\CouldNotUpdateOrderTotals;
use Mollie\Logger\LoggerInterface;
use Mollie\Repository\OrderRepositoryInterface;
use Mollie\Utility\ExceptionUtility;
use Mollie\Utility\NumberUtility;
use Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

class UpdateOrderTotalsAction
{
    const FILE_NAME = 'UpdateOrderTotalsAction';

    /** @var LoggerInterface */
    public $logger;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository, LoggerInterface $logger)
    {
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
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
            $this->logger->error(sprintf('%s - Failed to update order totals', self::FILE_NAME), [
                'exception' => ExceptionUtility::getExceptions($exception),
            ]);

            throw CouldNotUpdateOrderTotals::failedToUpdateOrderTotals($exception);
        }
    }
}
