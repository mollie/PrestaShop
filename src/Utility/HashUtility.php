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

namespace Mollie\Utility;

class HashUtility
{
	/**
	 * Hash password.
	 *
	 * @param string $passwd String to has
	 *
	 * @return string Hashed password
	 *
	 * @since 1.7.0
	 */
	public static function hash($passwd)
	{
		return md5(_COOKIE_KEY_ . $passwd);
	}
}
