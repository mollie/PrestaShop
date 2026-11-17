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

namespace Mollie\Service\ExpressComponent;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Handler\Certificate\CertificateHandlerInterface;
use Mollie\Logger\LoggerInterface;
use Mollie\Utility\ExceptionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * New installs have the Express Component and its Apple Pay button on, so no switch is ever toggled
 * to place the Apple Pay domain association file. Going live places it instead.
 */
class ExpressApplePayDomainFileService
{
    /** @var ConfigurationAdapter */
    private $configuration;
    /** @var CertificateHandlerInterface */
    private $certificateHandler;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ConfigurationAdapter $configuration,
        CertificateHandlerInterface $certificateHandler,
        LoggerInterface $logger
    ) {
        $this->configuration = $configuration;
        $this->certificateHandler = $certificateHandler;
        $this->logger = $logger;
    }

    /** A failure is logged only: the file may already be served, and it never blocks the caller. */
    public function placeForExpress(): void
    {
        if (!(int) $this->configuration->get(Config::MOLLIE_EXPRESS_COMPONENT_ENABLED)
            || !(int) $this->configuration->get(Config::MOLLIE_EXPRESS_COMPONENT_APPLEPAY)
        ) {
            return;
        }

        try {
            $this->certificateHandler->handle();
            $this->certificateHandler->refresh();
        } catch (\Throwable $exception) {
            $this->logger->warning('Apple Pay domain association file could not be placed for the express component when the shop went live', [
                'exceptions' => ExceptionUtility::getExceptions($exception),
            ]);
        }
    }
}
