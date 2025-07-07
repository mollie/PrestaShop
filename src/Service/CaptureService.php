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

if (!defined('_PS_VERSION_')) {
    exit;
}

class CaptureService
{
    const FILE_NAME = 'CaptureService';

    /**
     * @var Mollie
     */
    private $module;

    public function __construct(Mollie $module)
    {
        $this->module = $module;
    }

    /**
     * Capture a payment by transaction ID (Payments API)
     *
     * @param string $transactionId
     * @return array
     */
    public function doPaymentCapture($transactionId)
    {
        try {
            $payment = $this->module->getApiClient()->payments->get($transactionId);
            $this->module->getApiClient()->paymentCaptures->createForId($transactionId);

            return [
                'success' => true,
                'message' => '',
                'detailed' => '',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $this->module->l('The payment could not be captured!', self::FILE_NAME),
                'detailed' => $e->getMessage(),
            ];
        }
    }
}