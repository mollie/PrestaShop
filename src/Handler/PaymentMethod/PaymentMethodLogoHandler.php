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

namespace Mollie\Handler\PaymentMethod;

use Mollie\Logger\LoggerInterface;
use Mollie\Provider\CreditCardLogoProvider;
use Mollie\Utility\ExceptionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentMethodLogoHandler
{
    /** @var CreditCardLogoProvider */
    private $creditCardLogoProvider;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        CreditCardLogoProvider $creditCardLogoProvider,
        LoggerInterface $logger
    ) {
        $this->creditCardLogoProvider = $creditCardLogoProvider;
        $this->logger = $logger;
    }

    /**
     * Handle custom logo upload
     *
     * @param array $uploadedFile Uploaded file data from $_FILES
     *
     * @return array Response data with success status, message, and logo URL
     */
    public function handleLogoUpload(array $uploadedFile): array
    {
        try {
            $targetFile = $this->creditCardLogoProvider->getLocalLogoPath();
            $imageFileType = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));

            // Validate file was uploaded
            if (!isset($uploadedFile['error']) || $uploadedFile['error'] !== UPLOAD_ERR_OK) {
                return [
                    'success' => false,
                    'message' => 'No file uploaded or upload error',
                    'logoUrl' => null,
                ];
            }

            // Validate image format
            if (!in_array($imageFileType, ['jpg', 'jpeg', 'png'])) {
                return [
                    'success' => false,
                    'message' => 'Upload a .jpg or .png file.',
                    'logoUrl' => null,
                ];
            }

            // Validate image dimensions (max 256x64)
            $imageInfo = getimagesize($uploadedFile['tmp_name']);
            if ($imageInfo === false) {
                return [
                    'success' => false,
                    'message' => 'Invalid image file.',
                    'logoUrl' => null,
                ];
            }

            if ($imageInfo[0] > 256 || $imageInfo[1] > 64) {
                return [
                    'success' => false,
                    'message' => 'Image dimensions must be maximum 256x64 pixels.',
                    'logoUrl' => null,
                ];
            }

            // Create directory if needed
            $targetDir = dirname($targetFile);
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            // Move uploaded file
            if (move_uploaded_file($uploadedFile['tmp_name'], $targetFile)) {
                $logoUrl = $this->creditCardLogoProvider->getLogoPathUri() . '?' . time();

                return [
                    'success' => true,
                    'message' => basename($uploadedFile['name']),
                    'logoUrl' => $logoUrl,
                ];
            }

            return [
                'success' => false,
                'message' => 'Something went wrong when uploading your logo.',
                'logoUrl' => null,
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to upload custom logo', [
                'exception' => ExceptionUtility::getExceptions($e),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to upload custom logo',
                'logoUrl' => null,
            ];
        }
    }
}
