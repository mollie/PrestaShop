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
use Mollie\Subscription\Api\SubscriptionApi;
use Mollie\Subscription\DTO\UpdateSubscriptionData;
use Mollie\Subscription\Exception\CouldNotUpdateSubscription;
use Mollie\Subscription\Exception\MollieSubscriptionException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class UpdateSubscriptionAction
{
    /** @var SubscriptionApi */
    private $subscriptionApi;
    /** @var PrestaLoggerInterface */
    private $logger;

    public function __construct(
        SubscriptionApi $subscriptionApi,
        PrestaLoggerInterface $logger
    ) {
        $this->subscriptionApi = $subscriptionApi;
        $this->logger = $logger;
    }

    /**
     * @throws MollieSubscriptionException
     */
    public function run(UpdateSubscriptionData $data): void
    {
        $this->logger->info(sprintf('%s - Function called', __METHOD__));

        try {
            $this->subscriptionApi->updateSubscription($data);
        } catch (\Throwable $exception) {
            throw CouldNotUpdateSubscription::failedToUpdateSubscription($exception, $data->getSubscriptionId());
        }

        $this->logger->info(sprintf('%s - Function ended', __METHOD__));
    }
}
