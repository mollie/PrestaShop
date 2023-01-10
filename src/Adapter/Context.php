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

use Configuration as PrestashopConfiguration;
use Context as PrestashopContext;

class Context
{
    public function getShopId()
    {
        return (int) PrestashopContext::getContext()->shop->id;
    }

    public function getLanguageId()
    {
        return (int) PrestashopContext::getContext()->language->id;
    }

    public function getLanguageIso()
    {
        return (string) PrestashopContext::getContext()->currency->iso_code ?: 'en';
    }

    public function getCurrencyIso()
    {
        if (!PrestashopContext::getContext()->currency) {
            return '';
        }

        return (string) PrestashopContext::getContext()->currency->iso_code;
    }

    public function getCustomerId()
    {
        if (!PrestashopContext::getContext()->customer) {
            return 0;
        }

        return (int) PrestashopContext::getContext()->customer->id;
    }

    public function getCustomerEmail()
    {
        if (!PrestashopContext::getContext()->customer) {
            return '';
        }

        return PrestashopContext::getContext()->customer->email;
    }

    public function getShopDomain()
    {
        return (string) PrestashopContext::getContext()->shop->domain;
    }

    public function getShopName()
    {
        return (string) PrestashopContext::getContext()->shop->name;
    }

    public function getComputingPrecision()
    {
        return PrestashopConfiguration::get('PS_PRICE_DISPLAY_PRECISION');
    }

    public function getAdminLink($controllerName, array $params = [])
    {
        return (string) PrestashopContext::getContext()->link->getAdminLink($controllerName, true, [], $params);
    }
}
