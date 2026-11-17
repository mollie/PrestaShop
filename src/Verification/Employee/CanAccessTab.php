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

class CanAccessTab
{
    /**
     * @param Employee|null $employee
     */
    public function verify($employee, string $right, string $tabClassName): bool
    {
        if (!$employee instanceof Employee || !Validate::isLoadedObject($employee)) {
            return false;
        }

        return $employee->can($right, $tabClassName);
    }
}
