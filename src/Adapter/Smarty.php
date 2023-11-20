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

namespace Mollie\Adapter;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Smarty
{
    public function assign($tpl_var, $value = null, $nocache = false)
    {
        return \Context::getContext()->smarty->assign($tpl_var, $value, $nocache);
    }
}
