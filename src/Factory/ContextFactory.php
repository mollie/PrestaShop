<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author INVERTUS UAB www.invertus.eu  <support@invertus.eu>
 * @copyright Fruugo.com Limited
 * @license Fruugo
 */

namespace Mollie\Factory;

use Context;

class ContextFactory
{
    public static function getContext()
    {
        return Context::getContext();
    }

    public static function getLanguage()
    {
        return Context::getContext()->language;
    }

    public static function getCurrency()
    {
        return Context::getContext()->currency;
    }

    public static function getSmarty()
    {
        return Context::getContext()->smarty;
    }

    public static function getShop()
    {
        return Context::getContext()->shop;
    }

    public static function getController()
    {
        return Context::getContext()->controller;
    }

    public static function getCookie()
    {
        return Context::getContext()->cookie;
    }

    public static function getLink()
    {
        return Context::getContext()->link;
    }
}
