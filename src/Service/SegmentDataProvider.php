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

namespace Mollie\Service;

use Context;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Repository\PaymentMethodRepositoryInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SegmentDataProvider
{
    /** @var ConfigurationAdapter */
    private $configuration;
    /** @var PaymentMethodRepositoryInterface */
    private $paymentMethodRepository;

    public function __construct(
        ConfigurationAdapter $configuration,
        PaymentMethodRepositoryInterface $paymentMethodRepository
    ) {
        $this->configuration = $configuration;
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    public function getEnvironmentLabel(): string
    {
        return (int) $this->configuration->get(Config::MOLLIE_ENVIRONMENT) === Config::ENVIRONMENT_LIVE
            ? 'live'
            : 'test';
    }

    public function getEnabledMethodsCount(): int
    {
        $environment = (int) $this->configuration->get(Config::MOLLIE_ENVIRONMENT);
        $shopId = (int) Context::getContext()->shop->id;

        return $this->paymentMethodRepository->countEnabledMethods($environment, $shopId);
    }

    public function getDaysSinceInstall(): int
    {
        $timestamp = (int) $this->configuration->get(Config::MOLLIE_SEGMENT_INSTALL_TIMESTAMP);

        if ($timestamp <= 0) {
            return 0;
        }

        return (int) round((time() - $timestamp) / 86400);
    }

    public function hadSuccessfulPayment(): bool
    {
        return $this->paymentMethodRepository->hasAnySuccessfulPayment();
    }
}
