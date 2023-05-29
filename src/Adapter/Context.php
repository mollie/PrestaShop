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
    public function getLanguageId(): int
    {
        return (int) PrestashopContext::getContext()->language->id;
    }

    public function getLanguageIso(): string
    {
        return (string) PrestashopContext::getContext()->currency->iso_code ?: 'en';
    }

    public function getCurrencyIso(): string
    {
        /* @phpstan-ignore-next-line */
        if (!PrestashopContext::getContext()->currency) {
            return '';
        }

        return (string) PrestashopContext::getContext()->currency->iso_code;
    }

    public function getCustomerId(): int
    {
        /* @phpstan-ignore-next-line */
        if (!PrestashopContext::getContext()->customer) {
            return 0;
        }

        return (int) PrestashopContext::getContext()->customer->id;
    }

    public function getShopDomain(): string
    {
        return (string) PrestashopContext::getContext()->shop->domain;
    }

    public function getAdminLink($controllerName, array $params = []): string
    {
        return (string) PrestashopContext::getContext()->link->getAdminLink($controllerName, true, [], $params);
    }

    public function getCartProducts(): array
    {
        return PrestashopContext::getContext()->cart->getProducts();
    }

    public function getComputingPrecision(): int
    {
        if (method_exists(PrestashopContext::getContext(), 'getComputingPrecision')) {
            return PrestashopContext::getContext()->getComputingPrecision();
        }

        return (int) PrestashopConfiguration::get('PS_PRICE_DISPLAY_PRECISION');
    }

    public function getShopId(): int
    {
        return (int) PrestashopContext::getContext()->shop->id;
    }

    public function getCustomerAddressInvoiceId(): int
    {
        return (int) PrestashopContext::getContext()->cart->id_address_invoice;
    }

    public function getModuleLink(
        $module,
        $controller = 'default',
        array $params = [],
        $ssl = null,
        $idLang = null,
        $idShop = null,
        $relativeProtocol = false
    ): string {
        return (string) PrestashopContext::getContext()->link->getModuleLink(
            $module,
            $controller,
            $params,
            $ssl,
            $idLang,
            $idShop,
            $relativeProtocol
        );
    }

    public function getAddressInvoiceId(): int
    {
        return (int) PrestashopContext::getContext()->cart->id_address_invoice;
    }

    public function getLanguageLocale(): string
    {
        return (string) PrestashopContext::getContext()->language->locale;
    }

    public function getCountryId(): int
    {
        return (string) PrestashopContext::getContext()->country->id;
    }
}
