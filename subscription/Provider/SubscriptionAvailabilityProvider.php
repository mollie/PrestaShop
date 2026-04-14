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

namespace Mollie\Subscription\Provider;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Logger\LoggerInterface;
use Mollie\Repository\MolCustomerRepositoryInterface;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;
use Mollie\Utility\ExceptionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SubscriptionAvailabilityProvider
{
    const FILE_NAME = 'SubscriptionAvailabilityProvider';

    /** @var ConfigurationAdapter */
    private $configuration;
    /** @var MolCustomerRepositoryInterface */
    private $molCustomerRepository;
    /** @var RecurringOrderRepositoryInterface */
    private $recurringOrderRepository;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ConfigurationAdapter $configuration,
        MolCustomerRepositoryInterface $molCustomerRepository,
        RecurringOrderRepositoryInterface $recurringOrderRepository,
        LoggerInterface $logger
    ) {
        $this->configuration = $configuration;
        $this->molCustomerRepository = $molCustomerRepository;
        $this->recurringOrderRepository = $recurringOrderRepository;
        $this->logger = $logger;
    }

    /**
     * Check if subscription functionality should be available for a customer
     *
     * @param string $customerEmail
     *
     * @return bool Returns true if subscriptions are enabled OR customer has existing subscription orders
     *              Returns false on any error to fail safely
     */
    public function isAvailableForCustomer(string $customerEmail): bool
    {
        try {
            // Validate input
            if (empty($customerEmail)) {
                return false;
            }

            $isSubscriptionEnabled = (bool) $this->configuration->get(Config::MOLLIE_SUBSCRIPTION_ENABLED);

            // If subscriptions are enabled, always show
            if ($isSubscriptionEnabled) {
                return true;
            }

            // If subscriptions are disabled, check if customer has existing subscription orders
            return $this->hasExistingSubscriptionOrders($customerEmail);
        } catch (\Throwable $exception) {
            $this->logger->error(sprintf('%s - Error checking subscription availability', self::FILE_NAME), [
                'customer_email' => $customerEmail,
                'exceptions' => ExceptionUtility::getExceptions($exception),
            ]);

            // Fail safely - don't show subscription tab if there's an error
            return false;
        }
    }

    /**
     * Check if customer has existing subscription orders
     *
     * @param string $customerEmail
     *
     * @return bool
     *
     * @throws \PrestaShopException
     */
    private function hasExistingSubscriptionOrders(string $customerEmail): bool
    {
        try {
            $molCustomer = $this->molCustomerRepository->findOneBy([
                'email' => $customerEmail,
            ]);

            if (!$molCustomer) {
                return false;
            }

            $recurringOrders = $this->recurringOrderRepository->findBy([
                'mol_customer_id' => $molCustomer->customer_id,
            ]);

            return !empty($recurringOrders);
        } catch (\Throwable $exception) {
            // Log and re-throw to be caught by parent method
            $this->logger->error(sprintf('%s - Error checking existing subscription orders', self::FILE_NAME), [
                'customer_email' => $customerEmail,
                'exceptions' => ExceptionUtility::getExceptions($exception),
            ]);

            throw $exception;
        }
    }
}
