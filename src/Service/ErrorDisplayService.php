<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 */

namespace Mollie\Service;

use Context;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ErrorDisplayService
{
    public function showCookieError($id)
    {
        $context = Context::getContext();
        if (isset($context->cookie->$id)) {
            $context->controller->warning = $this->stripSlashesDeep(json_decode($context->cookie->$id));
            unset($context->cookie->$id);
            unset($_SERVER['HTTP_REFERER']);
        }
    }

    private function stripSlashesDeep($value)
    {
        $value = is_array($value) ?
            array_map('stripslashes', $value) :
            Tools::stripslashes($value);

        return $value;
    }
}
