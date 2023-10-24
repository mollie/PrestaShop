<?php

namespace Mollie\Subscription\Form\Options;

use Mollie\Utility\PsVersionUtility;
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
        if (PsVersionUtility::isPsVersionGreaterOrEqualTo(_PS_VERSION_, '1.7.8.0')) {
            return $this->subscriptionOptionsConfiguration->getConfiguration();
        }

        return ['subscription_options' => $this->subscriptionOptionsConfiguration->getConfiguration()];
    }

    /**
     * {@inheritdoc}
     */
    public function setData(array $data): array
    {
        if (PsVersionUtility::isPsVersionGreaterOrEqualTo(_PS_VERSION_, '1.7.8.0')) {
            return $this->subscriptionOptionsConfiguration->updateConfiguration($data);
        }

        return $this->subscriptionOptionsConfiguration->updateConfiguration($data['subscription_options']);
    }
}
