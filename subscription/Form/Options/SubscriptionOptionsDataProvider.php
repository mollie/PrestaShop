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

use Mollie\Utility\VersionUtility;
use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

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
        if (VersionUtility::isPsVersionGreaterOrEqualTo('1.7.8.0')) {
            return $this->subscriptionOptionsConfiguration->getConfiguration();
        }

        return ['subscription_options' => $this->subscriptionOptionsConfiguration->getConfiguration()];
    }

    /**
     * {@inheritdoc}
     */
    public function setData(array $data): array
    {
        if (VersionUtility::isPsVersionGreaterOrEqualTo('1.7.8.0')) {
            return $this->subscriptionOptionsConfiguration->updateConfiguration($data);
        }

        return $this->subscriptionOptionsConfiguration->updateConfiguration($data['subscription_options']);
    }
}
