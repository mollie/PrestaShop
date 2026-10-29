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
        $shopFilePath = $this->getShopFilePath();

        if (!FileUtility::fileExists($shopFilePath)) {
            return false;
        }

        $shopFile = (string) file_get_contents($shopFilePath);
        $bundledFile = (string) file_get_contents(self::APPLE_PAY_CERTIFICATE_FILE_LOCATION);
        $bundledPspId = $this->getPspId($bundledFile);

        if ($bundledPspId === '') {
            return $shopFile !== $bundledFile;
        }

        return $this->getPspId($shopFile) !== $bundledPspId;
    }

    /**
     * @throws ApplePayDirectCertificateCreation
     */
    public function handle()
    {
        /* Checks if certificate already exists in prestashop */
        if (FileUtility::fileExists($this->getShopFilePath())) {
            if ($this->hasConflict()) {
                throw new ApplePayDirectCertificateCreation($this->mollie->l('Apple Pay domain association file does not belong to Mollie. Please verify your domain configuration.', self::FILE_NAME), ApplePayDirectCertificateCreation::FILE_CONFLICT_EXCEPTION);
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
            $this->getShopFilePath()
        )) {
            throw new ApplePayDirectCertificateCreation($this->mollie->l('Failed to copy apple pay direct certificate', self::FILE_NAME), ApplePayDirectCertificateCreation::FILE_COPY_EXCEPTON);
        }
    }

    /**
     * Replaces an outdated Mollie file with the bundled one. A missing file and a file of
     * another provider are left untouched.
     *
     * @throws ApplePayDirectCertificateCreation when the outdated Mollie file can't be replaced
     */
    public function refresh()
    {
        $shopFilePath = $this->getShopFilePath();

        if (!FileUtility::fileExists($shopFilePath) || $this->hasConflict()) {
            return;
        }

        if (file_get_contents($shopFilePath) === file_get_contents(self::APPLE_PAY_CERTIFICATE_FILE_LOCATION)) {
            return;
        }

        // Apple keeps verifying the domain against the outdated file, so it is swapped in one
        // rename instead of being rewritten in place, where a failed write would truncate it.
        $temporaryFilePath = $shopFilePath . '.' . uniqid('', true) . '.tmp';

        $isReplaced = FileUtility::isWritable($this->serverRoot . self::APPLE_PAY_CERTIFICATE_FOLDER)
            && FileUtility::copyFile(self::APPLE_PAY_CERTIFICATE_FILE_LOCATION, $temporaryFilePath)
            && FileUtility::moveFile($temporaryFilePath, $shopFilePath);

        if ($isReplaced) {
            return;
        }

        if (FileUtility::fileExists($temporaryFilePath)) {
            FileUtility::deleteFile($temporaryFilePath);
        }

        throw new ApplePayDirectCertificateCreation('Failed to update the Apple Pay domain association file: ' . $shopFilePath, ApplePayDirectCertificateCreation::FILE_COPY_EXCEPTON);
    }

    private function getShopFilePath(): string
    {
        return $this->serverRoot . self::APPLE_PAY_CERTIFICATE_FOLDER . self::APPLE_PAY_CERTIFICATE_PS_FILE;
    }

    /**
     * Mollie replaces the file contents from time to time, but every version carries the same
     * Mollie PSP id, so the id tells an older Mollie file apart from another provider's file.
     */
    private function getPspId(string $contents): string
    {
        $hex = trim($contents);

        if (!preg_match('/^(?:[0-9a-fA-F]{2})+$/', $hex)) {
            return '';
        }

        $payload = json_decode((string) hex2bin($hex), true);

        if (!is_array($payload) || !isset($payload['pspId']) || !is_string($payload['pspId'])) {
            return '';
        }

        return $payload['pspId'];
    }
}
