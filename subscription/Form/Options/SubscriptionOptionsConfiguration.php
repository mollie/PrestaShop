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

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function validateConfiguration(array $configuration): bool
    {
        return isset(
            $configuration['enable_subscriptions'],
            $configuration['carrier']
        );
    }
}
