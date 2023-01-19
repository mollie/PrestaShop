<?php

declare(strict_types=1);

namespace Mollie\Subscription\Factory;

use Mollie\Subscription\DTO\GetSubscriptionData as GetSubscriptionDataDTO;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;

class GetSubscriptionDataFactory
{
    /** @var RecurringOrderRepositoryInterface */
    private $customerRepository;

    public function __construct(
        RecurringOrderRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
    }

    public function build(int $recurringOrderId): GetSubscriptionDataDTO
    {
        /** @var \MolRecurringOrder $subscription */
        $subscription = $this->customerRepository->findOneBy(['id_mol_recurring_order' => $recurringOrderId]);

        return new GetSubscriptionDataDTO($subscription->mollie_customer_id, $subscription->mollie_subscription_id);
    }
}
