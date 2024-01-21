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
use Mollie\Api\Types\OrderStatus;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Api\Types\RefundStatus;
use Mollie\Config\Config;

if (!defined('_PS_VERSION_')) {
    exit;
}

class LanguageService
{
    const FILE_NAME = 'LanguageService';

    /**
     * @var Mollie
     */
    private $module;

    public function __construct(Mollie $module)
    {
        $this->module = $module;
    }

    public function getLang()
    {
        return [
            PaymentStatus::STATUS_PAID => $this->module->l('Paid', self::FILE_NAME),
            OrderStatus::STATUS_COMPLETED => $this->module->l('Completed', self::FILE_NAME),
            PaymentStatus::STATUS_AUTHORIZED => $this->module->l('Authorized', self::FILE_NAME),
            PaymentStatus::STATUS_CANCELED => $this->module->l('Canceled', self::FILE_NAME),
            PaymentStatus::STATUS_EXPIRED => $this->module->l('Expired', self::FILE_NAME),
            RefundStatus::STATUS_REFUNDED => $this->module->l('Refunded', self::FILE_NAME),
            PaymentStatus::STATUS_OPEN => $this->module->l('Open', self::FILE_NAME),
            Config::MOLLIE_AWAITING_PAYMENT => $this->module->l('Awaiting', self::FILE_NAME),
            Mollie\Config\Config::PARTIAL_REFUND_CODE => $this->module->l('Partially refunded', self::FILE_NAME),
            Mollie\Config\Config::MOLLIE_CHARGEBACK => $this->module->l('Chargeback', self::FILE_NAME),
            'Payment fee' => $this->module->l('Payment fee', self::FILE_NAME),
            'Shipping' => $this->module->l('Shipping', self::FILE_NAME),
            'Gift wrapping' => $this->module->l('Gift wrapping', self::FILE_NAME),
            'This payment method is not available.' => $this->module->l('This payment method is not available.', self::FILE_NAME),
        ];
    }

    public function lang($str)
    {
        $lang = $this->getLang();
        if (array_key_exists($str, $lang)) {
            return $lang[$str];
        }

        return $str;
    }
}
