<?php

namespace Mollie\Subscription\Form\Options;

use Mollie\Config\Config;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;

final class SubscriptionOptionsConfiguration implements DataConfigurationInterface
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(): array
    {
        return [
            'carrier' => $this->configuration->getInt(Config::MOLLIE_SUBSCRIPTION_ORDER_CARRIER_ID),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function updateConfiguration(array $configuration): array
    {
        if (!$this->validateConfiguration($configuration)) {
            return [];
        }

        $this->configuration->set(
            Config::MOLLIE_SUBSCRIPTION_ORDER_CARRIER_ID,
            $configuration['carrier']
        );

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function validateConfiguration(array $configuration): bool
    {
        return isset(
            $configuration['carrier']
        );
    }
}
