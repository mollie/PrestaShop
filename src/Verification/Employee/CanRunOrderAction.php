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

namespace Mollie\Verification\Employee;

use Employee;
use Validate;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CanRunOrderAction
{
    const ORDER_ACTIONS = [
        'refund',
        'refundAll',
        'capture',
        'captureAll',
        'ship',
        'shipAll',
        'cancel',
        'cancelAll',
    ];

    /**
     * @param Employee|null $employee
     */
    public function verify(string $action, $employee): bool
    {
        if (!in_array($action, self::ORDER_ACTIONS, true)) {
            return true;
        }

        if (!$employee instanceof Employee || !Validate::isLoadedObject($employee)) {
            return false;
        }

        return (bool) $employee->can('edit', 'AdminOrders');
    }
}
