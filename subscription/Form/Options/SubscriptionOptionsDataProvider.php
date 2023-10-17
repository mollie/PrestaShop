<?php

namespace Mollie\Subscription\Form\Options;

use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;

final class SubscriptionOptionsDataProvider implements FormDataProviderInterface
{
    /** @var DataConfigurationInterface */
    private $subscriptionOptionsConfiguration;

    public function __construct(
        DataConfigurationInterface $subscriptionOptionsConfiguration
    ) {
        $this->subscriptionOptionsConfiguration = $subscriptionOptionsConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        return $this->subscriptionOptionsConfiguration->getConfiguration();
    }

    /**
     * {@inheritdoc}
     */
    public function setData(array $data): array
    {
        return $this->subscriptionOptionsConfiguration->updateConfiguration($data);
    }
}
