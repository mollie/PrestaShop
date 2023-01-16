<?php

declare(strict_types=1);

namespace Mollie\Subscription\Factory;

use Mollie\Subscription\DTO\CancelSubscriptionData as CancelSubscriptionDataDTO;
use Mollie\Subscription\Repository\SubscriptionRepositoryInterface;

class CancelSubscriptionDataFactory
{
    /** @var SubscriptionRepositoryInterface */
    private $customerRepository;

    public function __construct(
        SubscriptionRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
    }

    public function build(int $subscriptionId): CancelSubscriptionDataDTO
    {
        /** @var \MolSubRecurringOrder $subscription */
        $subscription = $this->customerRepository->findOneBy(['id_mol_sub_recurring_order' => $subscriptionId]);

        return new CancelSubscriptionDataDTO($subscription->mollie_customer_id, $subscription->mollie_sub_id);
    }
}
