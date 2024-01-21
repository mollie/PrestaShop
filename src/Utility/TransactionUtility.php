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

namespace Mollie\Utility;

use Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

class TransactionUtility
{
    public static function isOrderTransaction($transactionId)
    {
        return 'ord' === Tools::substr($transactionId, 0, 3);
    }
}
