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

if (!defined('_PS_VERSION_')) {
    exit;
}

trait DeniesAjaxWithoutTabRight
{
    private function denyAjaxWithoutTabRight(string $right, string $tabClassName): bool
    {
        if ((new CanAccessTab())->verify($this->context->employee, $right, $tabClassName)) {
            return false;
        }

        $message = 'edit' === $right
            ? $this->trans('You do not have permission to edit this.', [], 'Admin.Notifications.Error')
            : $this->trans('You do not have permission to view this.', [], 'Admin.Notifications.Error');

        $this->ajaxRender(json_encode([
            'success' => false,
            'message' => $message,
        ]));

        return true;
    }
}
