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

namespace Mollie\Exception;

use Exception;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ShipmentCannotBeSentException extends Exception
{
    public const NO_SHIPPING_INFORMATION = 1;
    public const ORDER_HAS_NO_PAYMENT_INFORMATION = 2;
    public const PAYMENT_IS_NOT_ORDER = 3;

    /**
     * @var string
     */
    private $orderReference;

    public function __construct($message, $code, $orderId, Exception $previous = null)
    {
        $this->orderReference = $orderId;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getOrderReference()
    {
        return $this->orderReference;
    }
}
