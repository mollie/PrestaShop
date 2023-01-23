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

namespace Mollie\Factory;

use Context;

class ContextFactory
{
    public static function getContext(): Context
    {
        return Context::getContext();
    }

    public static function getLanguage(): \Language
    {
        return Context::getContext()->language;
    }

    public static function getCurrency(): \Currency
    {
        return Context::getContext()->currency;
    }

    public static function getSmarty(): \Smarty
    {
        return Context::getContext()->smarty;
    }

    public static function getShop(): \Shop
    {
        return Context::getContext()->shop;
    }

    /**
     * @return \AdminController|\FrontController
     */
    public static function getController()
    {
        return Context::getContext()->controller;
    }

    public static function getCookie(): \Cookie
    {
        return Context::getContext()->cookie;
    }

    public static function getLink(): \Link
    {
        return Context::getContext()->link;
    }

    public static function getCountry(): \Country
    {
        return Context::getContext()->country;
    }

    public static function getCustomer(): \Customer
    {
        return Context::getContext()->customer;
    }

    public static function getCart(): \Cart
    {
        return Context::getContext()->cart;
    }
}
