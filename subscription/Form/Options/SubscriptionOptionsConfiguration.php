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

namespace Mollie\Subscription\Form\Options;

use Mollie\Config\Config;
use Mollie\Subscription\Config\Config as SubscriptionConfig;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

final class SubscriptionOptionsConfiguration implements DataConfigurationInterface
{
    /**
     * @var Configuration
     */
    private $configuration;

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
            'enable_subscriptions' => $this->configuration->getBoolean(Config::MOLLIE_SUBSCRIPTION_ENABLED),
            'carrier' => $this->configuration->getInt(Config::MOLLIE_SUBSCRIPTION_ORDER_CARRIER_ID),
            'attribute_group_id' => $this->configuration->getInt(SubscriptionConfig::SUBSCRIPTION_ATTRIBUTE_GROUP)
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
            Config::MOLLIE_SUBSCRIPTION_ENABLED,
            (int) $configuration['enable_subscriptions']
        );

        $this->configuration->set(
            Config::MOLLIE_SUBSCRIPTION_ORDER_CARRIER_ID,
            (int) $configuration['carrier']
        );

        $this->configuration->set(
            SubscriptionConfig::SUBSCRIPTION_ATTRIBUTE_GROUP,
            (int) $configuration['attribute_group_id']
        );

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function validateConfiguration(array $configuration): bool
    {
        return isset(
            $configuration['enable_subscriptions'],
            $configuration['carrier'],
            $configuration['attribute_group_id']
        );
    }
}
