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

    public function getProductLink($product): string
    {
        return (string) PrestashopContext::getContext()->link->getProductLink($product);
    }

    public function getImageLink($name, $ids, $type = null): string
    {
        return (string) PrestashopContext::getContext()->link->getImageLink($name, $ids, $type);
    }
}
