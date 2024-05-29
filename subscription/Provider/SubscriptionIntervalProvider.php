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

namespace Mollie\Subscription\Provider;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Subscription\Config\Config;
use Mollie\Subscription\DTO\Object\Interval;
use Mollie\Subscription\Exception\SubscriptionIntervalException;
use Mollie\Subscription\Repository\CombinationRepositoryInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SubscriptionIntervalProvider
{
    /** @var ConfigurationAdapter */
    private $configuration;
    /** @var CombinationRepositoryInterface */
    private $combinationRepository;

    public function __construct(
        ConfigurationAdapter $configuration,
        CombinationRepositoryInterface $combinationRepository
    ) {
        $this->configuration = $configuration;
        $this->combinationRepository = $combinationRepository;
    }

    /**
     * Returns subscription interval if combination has attribute mapped with subscription
     *
     * @throws SubscriptionIntervalException
     */
    public function getSubscriptionInterval(int $productAttributeId): Interval
    {
        /** @var \Combination|null $combination */
        $combination = $this->combinationRepository->findOneBy([
            'id_product_attribute' => $productAttributeId,
        ]);

        if (!$combination) {
            throw SubscriptionIntervalException::failedToFindCombination($productAttributeId);
        }

        foreach ($combination->getWsProductOptionValues() as $attribute) {
            switch ($attribute['id']) {
                case $this->configuration->get(Config::SUBSCRIPTION_ATTRIBUTE_DAILY):
                    return Config::getSubscriptionIntervals()[Config::SUBSCRIPTION_ATTRIBUTE_DAILY];
                case $this->configuration->get(Config::SUBSCRIPTION_ATTRIBUTE_WEEKLY):
                    return Config::getSubscriptionIntervals()[Config::SUBSCRIPTION_ATTRIBUTE_WEEKLY];
                case $this->configuration->get(Config::SUBSCRIPTION_ATTRIBUTE_MONTHLY):
                    return Config::getSubscriptionIntervals()[Config::SUBSCRIPTION_ATTRIBUTE_MONTHLY];
                case $this->configuration->get(Config::SUBSCRIPTION_ATTRIBUTE_YEARLY):
                    return Config::getSubscriptionIntervals()[Config::SUBSCRIPTION_ATTRIBUTE_YEARLY];
            }
        }

        throw SubscriptionIntervalException::failedToFindMatchingInterval($productAttributeId);
    }
}
