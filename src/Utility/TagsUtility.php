<?php
/**
 * Copyright (c) 2012-2020, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 *
 * @category   Mollie
 *
 * @see       https://www.mollie.nl
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
