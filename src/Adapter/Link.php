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

namespace Mollie\Adapter;

use Context as PrestashopContext;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Link
{
    public function getAdminLink($controller, $withToken = true, $sfRouteParams = [], $params = [])
    {
        return PrestashopContext::getContext()->link->getAdminLink($controller, $withToken, $sfRouteParams, $params);
    }

    public function getPageLink($controller, $ssl = null, $idLang = null, $request = null, $requestUrlEncode = false, $idShop = null, $relativeProtocol = false)
    {
        return PrestashopContext::getContext()->link->getPageLink($controller, $ssl, $idLang, $request, $requestUrlEncode, $idShop, $relativeProtocol);
    }

    public function getModuleLink(
        $module,
        $controller = 'default',
        array $params = [],
        $ssl = null,
        $idLang = null,
        $idShop = null,
        $relativeProtocol = false
    ) {
        return PrestashopContext::getContext()->link->getModuleLink($module, $controller, $params, $ssl, $idLang, $idShop, $relativeProtocol);
    }
}
