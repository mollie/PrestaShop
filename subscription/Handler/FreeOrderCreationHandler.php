<?php

declare(strict_types=1);

namespace Mollie\Subscription\Handler;

use Mollie\Subscription\Api\PaymentApi;
use Mollie\Subscription\Factory\CreateFreeOrderDataFactory;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;

class FreeOrderCreationHandler
{
    /** @var RecurringOrderRepositoryInterface */
    private $recurringOrderRepository;
    private $createFreeOrderDataFactory;
    /** @var PaymentApi */
    private $orderApi;

    public function __construct(
        RecurringOrderRepositoryInterface $recurringOrderRepository,
        CreateFreeOrderDataFactory $createFreeOrderDataFactory,
        PaymentApi $orderApi
    ) {
        $this->recurringOrderRepository = $recurringOrderRepository;
        $this->createFreeOrderDataFactory = $createFreeOrderDataFactory;
        $this->orderApi = $orderApi;
    }

    public function handle(int $recurringOrderId, string $newMethod): string
    {
        $recurringOrder = $this->recurringOrderRepository->findOneBy(['id_mol_recurring_order' => $recurringOrderId]);
        $newFreeOrderData = $this->createFreeOrderDataFactory->build($recurringOrder, $newMethod);
        $molPayment = $this->orderApi->createFreePayment($newFreeOrderData);

        return $molPayment->getCheckoutUrl();
    }
}
