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

namespace Mollie\PsCompat;

if (!defined('_PS_VERSION_')) {
    exit;
}

if (false) {
    /**
     * Stub for composer's classmap scanner. The real symbol is created via
     * class_alias below so the parent resolves to whichever base admin
     * controller exists in the current PrestaShop version.
     */
    class AdminBaseController
    {
    }
}

if (!class_exists(AdminBaseController::class, false)) {
    if (class_exists(\PrestaShopBundle\Controller\Admin\PrestaShopAdminController::class)) {
        class_alias(\PrestaShopBundle\Controller\Admin\PrestaShopAdminController::class, AdminBaseController::class);
    } else {
        class_alias(\PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController::class, AdminBaseController::class);
    }
}
