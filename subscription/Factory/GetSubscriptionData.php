<?php

declare(strict_types=1);

namespace Mollie\Subscription\Factory;

use Mollie\Subscription\DTO\GetSubscriptionData as GetSubscriptionDataDTO;
use Mollie\Subscription\Repository\SubscriptionRepositoryInterface;

class GetSubscriptionData
{
    /** @var SubscriptionRepositoryInterface */
    private $customerRepository;

    public function __construct(
        SubscriptionRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
    }

    public function build(int $subscriptionId): GetSubscriptionDataDTO
    {
        /** @var \MolSubRecurringOrder $subscription */
        $subscription = $this->customerRepository->findOneBy(['id_mol_sub_recurring_order' => $subscriptionId]);

        return new GetSubscriptionDataDTO($subscription->mollie_customer_id, $subscription->mollie_sub_id);
    }
}
