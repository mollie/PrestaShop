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

namespace Mollie\Handler\Certificate;

use Mollie;
use Mollie\Handler\Certificate\Exception\ApplePayDirectCertificateCreation;
use Mollie\Utility\FileUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ApplePayDirectCertificateHandler implements CertificateHandlerInterface
{
    const FILE_NAME = 'ApplePayDirectCertificateHandler';

    const APPLE_PAY_CERTIFICATE_PS_FILE = 'apple-developer-merchantid-domain-association';
    const APPLE_PAY_CERTIFICATE_FOLDER = '/.well-known/';
    const APPLE_PAY_CERTIFICATE_FILE_LOCATION = __DIR__ . '/Files/apple-developer-merchantid-domain-association';

    /**
     * @var Mollie
     */
    private $mollie;
    private $serverRoot;

    public function __construct(Mollie $mollie)
    {
        $this->mollie = $mollie;
        $this->serverRoot = $_SERVER['DOCUMENT_ROOT'];
    }

    /**
     * Checks if an existing domain association file belongs to another provider.
     *
     * @return bool true if the file exists and does not belong to Mollie
     */
    public function hasConflict()
    {
        $existingFilePath = $this->serverRoot . self::APPLE_PAY_CERTIFICATE_FOLDER . self::APPLE_PAY_CERTIFICATE_PS_FILE;

        if (!FileUtility::fileExists($existingFilePath)) {
            return false;
        }

        return file_get_contents($existingFilePath) !== file_get_contents(self::APPLE_PAY_CERTIFICATE_FILE_LOCATION);
    }

    /**
     * @throws ApplePayDirectCertificateCreation
     */
    public function handle()
    {
        /* Checks if certificate already exists in prestashop */
        $existingFilePath = $this->serverRoot . self::APPLE_PAY_CERTIFICATE_FOLDER . self::APPLE_PAY_CERTIFICATE_PS_FILE;
        if (FileUtility::fileExists($existingFilePath)) {
            if ($this->hasConflict()) {
                throw new ApplePayDirectCertificateCreation(
                    $this->mollie->l('Apple Pay domain association file does not belong to Mollie. Please verify your domain configuration.', self::FILE_NAME),
                    ApplePayDirectCertificateCreation::FILE_CONFLICT_EXCEPTION
                );
            }

            return;
        }

        /* Checks if certification in our module exists */
        if (!FileUtility::fileExists(self::APPLE_PAY_CERTIFICATE_FILE_LOCATION)) {
            return;
        }

        /*  creates dir for certification in ps if it doesn't exist. Throws exception if permission is missing and dir can't be created */
        if (!FileUtility::createDir($this->serverRoot . self::APPLE_PAY_CERTIFICATE_FOLDER)) {
            throw new ApplePayDirectCertificateCreation($this->mollie->l('Failed to create dir for apple pay direct certificate', self::FILE_NAME), ApplePayDirectCertificateCreation::DIR_CREATION_EXCEPTON);
        }

        $wellKnownFolderLocation = $this->serverRoot . self::APPLE_PAY_CERTIFICATE_FOLDER;
        /* Checks if folder has write permissions */
        if (!FileUtility::isWritable($wellKnownFolderLocation)) {
            throw new ApplePayDirectCertificateCreation($this->mollie->l('Can\'t create folder because of missing write permissions: ', self::FILE_NAME) . $wellKnownFolderLocation, ApplePayDirectCertificateCreation::DIR_CREATION_EXCEPTON);
        }

        /* copies certificate from module to prestashop */
        if (!FileUtility::copyFile(
            self::APPLE_PAY_CERTIFICATE_FILE_LOCATION,
            $this->serverRoot . self::APPLE_PAY_CERTIFICATE_FOLDER . self::APPLE_PAY_CERTIFICATE_PS_FILE
        )) {
            throw new ApplePayDirectCertificateCreation($this->mollie->l('Failed to copy apple pay direct certificate', self::FILE_NAME), ApplePayDirectCertificateCreation::FILE_COPY_EXCEPTON);
        }
    }
}
