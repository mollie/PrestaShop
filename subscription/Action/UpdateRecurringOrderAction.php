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

namespace Mollie\Subscription\Action;

use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Subscription\DTO\UpdateRecurringOrderData;
use Mollie\Subscription\Exception\CouldNotUpdateRecurringOrder;
use Mollie\Subscription\Exception\MollieSubscriptionException;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;
use Mollie\Subscription\Utility\ClockInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class UpdateRecurringOrderAction
{
    /** @var PrestaLoggerInterface */
    private $logger;
    /** @var ClockInterface */
    private $clock;
    /** @var RecurringOrderRepositoryInterface */
    private $recurringOrderRepository;

    public function __construct(
        PrestaLoggerInterface $logger,
        ClockInterface $clock,
        RecurringOrderRepositoryInterface $recurringOrderRepository
    ) {
        $this->logger = $logger;
        $this->clock = $clock;
        $this->recurringOrderRepository = $recurringOrderRepository;
    }

    /**
     * @throws MollieSubscriptionException
     */
    public function run(UpdateRecurringOrderData $data): \MolRecurringOrder
    {
        $this->logger->debug(sprintf('%s - Function called', __METHOD__));

        try {
            /** @var ?\MolRecurringOrder $recurringOrder */
            $recurringOrder = $this->recurringOrderRepository->findOneBy([
                'id_mol_recurring_order' => $data->getMollieRecurringOrderId(),
            ]);
        } catch (\Throwable $exception) {
            throw CouldNotUpdateRecurringOrder::unknownError($exception);
        }

        if (!$recurringOrder) {
            throw CouldNotUpdateRecurringOrder::failedToFindRecurringOrder($data->getMollieRecurringOrderId());
        }

        try {
            /*
             * NOTE: When more properties will be needed to update, pass them up as nullable parameters.
             */
            $recurringOrder->total_tax_incl = $data->getSubscriptionTotalAmount();
            $recurringOrder->date_update = $this->clock->getCurrentDate();

            $recurringOrder->update();
        } catch (\Throwable $exception) {
            throw CouldNotUpdateRecurringOrder::unknownError($exception);
        }

        $this->logger->debug(sprintf('%s - Function ended', __METHOD__));

        return $recurringOrder;
    }
}
