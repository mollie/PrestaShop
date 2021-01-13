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

class TagsUtility
{
	/**
	 * Post process tags in (translated) strings.
	 *
	 * @param string $string
	 * @param array $tags
	 *
	 * @return string
	 *
	 * @since 3.2.0
	 */
	public static function ppTags($string, $tags = [])
	{
		// If tags were explicitly provided, we want to use them *after* the translation string is escaped.
		if (!empty($tags)) {
			foreach ($tags as $index => $tag) {
				// Make positions start at 1 so that it behaves similar to the %1$d etc. sprintf positional params
				$position = $index + 1;
				// extract tag name
				$match = [];
				if (preg_match('/^\s*<\s*(\w+)/', $tag, $match)) {
					$opener = $tag;
					$closer = '</' . $match[1] . '>';

					$string = str_replace('[' . $position . ']', $opener, $string);
					$string = str_replace('[/' . $position . ']', $closer, $string);
					$string = str_replace('[' . $position . '/]', $opener . $closer, $string);
				}
			}
		}

		return $string;
	}
}
