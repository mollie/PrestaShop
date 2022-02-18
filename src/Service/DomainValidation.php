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

namespace Mollie\Service;

use Mollie;

class DomainValidation
{
    private const APPLE_PAY_CERTIFICATE_PS_FILE = 'apple-developer-merchantid-domain-association';
    private const APPLE_PAY_CERTIFICATE_FOLDER = _PS_ROOT_DIR_ . '/.well-known/';
    /**
     * @var Mollie
     */
    private $module;

    public function __construct(Mollie $module)
    {
        $this->module = $module;
    }

    private function isFolderWritable(string $folderUrl)
    {
        if (!file_exists(self::APPLE_PAY_CERTIFICATE_FOLDER . self::APPLE_PAY_CERTIFICATE_PS_FILE)) {
            if (is_writable(_PS_ROOT_DIR_)) {
                return true;
            }
        }

        return false;
    }

    public function createApplePayCertificate()
    {
        if (!$this->isFolderWritable(self::APPLE_PAY_CERTIFICATE_FOLDER)) {
            return;
        }

        if (!mkdir(self::APPLE_PAY_CERTIFICATE_FOLDER) && !is_dir(self::APPLE_PAY_CERTIFICATE_FOLDER)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', self::APPLE_PAY_CERTIFICATE_FOLDER));
        }

        if (!copy(
            $this->module->getLocalPath() . self::APPLE_DEVELOPER_ASSOCIATION,
            self::APPLE_PAY_CERTIFICATE_PS_FILE_PATH
        )) {
            $this->applePayEnablingError($configuration, $errorNotification, $tools);
        }
    }

    public function postProcess()
    {
        parent::postProcess();
        /** @var Tools $tools */
        $tools = $this->module->getService(Tools::class);

        if (!$tools->isSubmit('submitOptionsconfiguration')) {
            return;
        }

        $isApplePayEnabled = (int) $tools->getValue(Config::APPLE_PAY_ENABLED);

        if (!$isApplePayEnabled) {
            return;
        }

        /** @var Configuration $configuration */
        $configuration = $this->module->getService(Configuration::class);

        /** @var ErrorNotification $errorNotification */
        $errorNotification = $this->module->getService(ErrorNotification::class);

        if (!file_exists(self::APPLE_PAY_CERTIFICATE_PS_FILE_PATH)) {
            if (is_writable(_PS_ROOT_DIR_)) {
                if (!file_exists(_PS_ROOT_DIR_ . '/.well-known')) {
                    mkdir(_PS_ROOT_DIR_ . '/.well-known');
                }
                if (!copy(
                    $this->module->getLocalPath() . self::APPLE_DEVELOPER_ASSOCIATION,
                    self::APPLE_PAY_CERTIFICATE_PS_FILE_PATH
                )) {
                    $this->applePayEnablingError($configuration, $errorNotification, $tools);
                }
            } else {
                $this->applePayEnablingError($configuration, $errorNotification, $tools);
            }
        }

        // apple pay domain can be registered multiple times.

        /** @var ApplePayApiRepositoryInterface $applePayApiRepo */
        $applePayApiRepo = $this->module->getService(ApplePayApiRepositoryInterface::class);
        $domain = $tools->getShopDomain();

        try {
            $applePayApiRepo->registerDomain($domain);
        } catch (SquareApiException $e) {
            $this->errors[] = sprintf($this->l('Unable to register Apple Pay domain "%s". Try again'), $domain);
        }
    }
}
