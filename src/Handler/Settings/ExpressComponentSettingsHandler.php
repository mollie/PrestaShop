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

namespace Mollie\Handler\Settings;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Handler\Certificate\CertificateHandlerInterface;
use Mollie\Logger\LoggerInterface;
use Mollie\Utility\ExceptionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Saves the Express Component master switch. The per-wallet buttons are saved from each
 * method's own card (PaymentMethodSettingsHandler); Apple Pay Direct keeps its own switches.
 */
class ExpressComponentSettingsHandler
{
    const FILE_NAME = 'ExpressComponentSettingsHandler';

    const FLAGS = [
        'enabled' => Config::MOLLIE_EXPRESS_COMPONENT_ENABLED,
        'applepay' => Config::MOLLIE_EXPRESS_COMPONENT_APPLEPAY,
        'googlepay' => Config::MOLLIE_EXPRESS_COMPONENT_GOOGLEPAY,
        'paypal' => Config::MOLLIE_EXPRESS_COMPONENT_PAYPAL,
    ];

    /** @var ConfigurationAdapter */
    private $configuration;
    /** @var CertificateHandlerInterface */
    private $applePayDirectCertificateHandler;
    /** @var LoggerInterface */
    private $logger;
    /** @var \Mollie */
    private $module;

    public function __construct(
        ConfigurationAdapter $configuration,
        CertificateHandlerInterface $applePayDirectCertificateHandler,
        LoggerInterface $logger,
        \Mollie $module
    ) {
        $this->configuration = $configuration;
        $this->applePayDirectCertificateHandler = $applePayDirectCertificateHandler;
        $this->logger = $logger;
        $this->module = $module;
    }

    /**
     * @return array{expressComponent: array<string, bool>, warning: string|null}
     */
    public function handle(array $settings): array
    {
        $enabled = (bool) ($settings['enabled'] ?? false);
        $this->configuration->updateValue(Config::MOLLIE_EXPRESS_COMPONENT_ENABLED, $enabled ? 1 : 0);

        $warning = null;

        // The per-wallet buttons are saved from each method's own card; only the Apple Pay
        // domain file needs placing here, in case that button was switched on before the component.
        if ($enabled && (int) $this->configuration->get(Config::MOLLIE_EXPRESS_COMPONENT_APPLEPAY)) {
            $warning = $this->placeApplePayDomainFile();
        }

        return [
            'expressComponent' => $this->getSettings(),
            'warning' => $warning,
        ];
    }

    /**
     * @return array<string, bool>
     */
    public function getSettings(): array
    {
        $settings = [];

        foreach (self::FLAGS as $key => $configKey) {
            $settings[$key] = (bool) (int) $this->configuration->get($configKey);
        }

        return $settings;
    }

    public function isEnabled(): bool
    {
        return (bool) (int) $this->configuration->get(Config::MOLLIE_EXPRESS_COMPONENT_ENABLED);
    }

    /**
     * Apple Pay in the component needs the same domain association file as Apple Pay Direct.
     * A failure is reported, not fatal: Google Pay and PayPal work without it.
     */
    private function placeApplePayDomainFile(): ?string
    {
        try {
            $this->applePayDirectCertificateHandler->handle();
            $this->applePayDirectCertificateHandler->refresh();

            return null;
        } catch (\Throwable $exception) {
            $this->logger->warning(sprintf('%s - Apple Pay domain association file could not be placed', self::FILE_NAME), [
                'exceptions' => ExceptionUtility::getExceptions($exception),
            ]);

            return $this->module->l('The Apple Pay domain verification file could not be placed. Apple Pay will not show in the Express Component until the file is served at /.well-known/apple-developer-merchantid-domain-association. Check the module logs.', self::FILE_NAME);
        }
    }
}
