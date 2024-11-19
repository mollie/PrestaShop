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

use Mollie\Factory\ModuleFactory;
use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Subscription\Api\Request\UpdateSubscriptionRequest;
use Mollie\Subscription\Api\SubscriptionApi;
use Mollie\Subscription\DTO\UpdateSubscriptionData;
use Mollie\Subscription\Exception\CouldNotUpdateSubscription;
use Mollie\Subscription\Exception\MollieSubscriptionException;
use Mollie\Utility\SecureKeyUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class UpdateSubscriptionAction
{
    /** @var SubscriptionApi */
    private $subscriptionApi;
    /** @var PrestaLoggerInterface */
    private $logger;
    /** @var \Mollie */
    private $module;

    public function __construct(
        SubscriptionApi $subscriptionApi,
        PrestaLoggerInterface $logger,
        ModuleFactory $moduleFactory
    ) {
        $this->subscriptionApi = $subscriptionApi;
        $this->logger = $logger;
        $this->module = $moduleFactory->getModule();
    }

    /**
     * @throws MollieSubscriptionException
     */
    public function run(UpdateSubscriptionData $data): void
    {
        $this->logger->info(sprintf('%s - Function called', __METHOD__));

        $secureKey = SecureKeyUtility::generateReturnKey(
            $data->getCustomerId(),
            $data->getCartId(),
            $this->module->name
        );

        $metadata = [
            'secure_key' => $secureKey,
            'subscription_carrier_id' => $data->getSubscriptionCarrierId(),
        ];

        $updateSubscriptionData = new UpdateSubscriptionRequest(
            $data->getMollieCustomerId(),
            $data->getMollieSubscriptionId(),
            null,
            $metadata,
            $data->getOrderAmount()
        );

        try {
            $this->subscriptionApi->updateSubscription($updateSubscriptionData);
        } catch (\Throwable $exception) {
            throw CouldNotUpdateSubscription::failedToUpdateSubscription($exception, $data->getMollieSubscriptionId());
        }

        $this->logger->info(sprintf('%s - Function ended', __METHOD__));
    }
}
