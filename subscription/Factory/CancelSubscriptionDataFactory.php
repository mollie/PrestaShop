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

declare(strict_types=1);

namespace Mollie\Subscription\Factory;

use Mollie\Subscription\DTO\CancelSubscriptionData as CancelSubscriptionDataDTO;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CancelSubscriptionDataFactory
{
    /** @var RecurringOrderRepositoryInterface */
    private $customerRepository;

    public function __construct(
        RecurringOrderRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
    }

    public function build(int $subscriptionId): CancelSubscriptionDataDTO
    {
        /** @var \MolRecurringOrder $subscription */
        $subscription = $this->customerRepository->findOneBy(['id_mol_recurring_order' => $subscriptionId]);

        return new CancelSubscriptionDataDTO($subscription->mollie_customer_id, $subscription->mollie_subscription_id);
    }
}
