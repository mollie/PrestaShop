<?php

declare(strict_types=1);

namespace Mollie\Subscription\Provider;

use Combination;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Subscription\Config\Config;
use Mollie\Subscription\DTO\Object\Interval;
use Mollie\Subscription\Exception\SubscriptionIntervalException;

class SubscriptionInterval
{
    /** @var ConfigurationAdapter */
    private $configuration;

    public function __construct(ConfigurationAdapter $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Returns subscription interval if combination has attribute mapped with subscription
     *
     * @throws SubscriptionIntervalException
     */
    public function getSubscriptionInterval(Combination $combination): Interval
    {
        foreach ($combination->getWsProductOptionValues() as $attribute) {
            switch ($attribute['id']) {
                case $this->configuration->get(Config::SUBSCRIPTION_ATTRIBUTE_DAILY):
                    return Config::getSubscriptionIntervals()[Config::SUBSCRIPTION_ATTRIBUTE_DAILY];
                case $this->configuration->get(Config::SUBSCRIPTION_ATTRIBUTE_WEEKLY):
                    return Config::getSubscriptionIntervals()[Config::SUBSCRIPTION_ATTRIBUTE_WEEKLY];
                case $this->configuration->get(Config::SUBSCRIPTION_ATTRIBUTE_MONTHLY):
                    return Config::getSubscriptionIntervals()[Config::SUBSCRIPTION_ATTRIBUTE_MONTHLY];
            }
        }

        throw new SubscriptionIntervalException(sprintf('No interval exists for this %s attribute', $combination->id));
    }
}
