<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
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

	public static function getCountry()
	{
		return Context::getContext()->country;
	}
}
