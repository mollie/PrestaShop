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

class Link
{
    public function getAdminLink($controller, $withToken = true, $sfRouteParams = [], $params = [])
    {
        return PrestashopContext::getContext()->link->getAdminLink($controller, $withToken, $sfRouteParams, $params);
    }
}
