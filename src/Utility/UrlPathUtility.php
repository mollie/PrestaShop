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

use Tools;

class UrlPathUtility
{
	/**
	 * @param string      $mediaUri
	 * @param string|null $cssMediaType
	 *
	 * @return array|bool|mixed|string
	 *
	 * @since   1.0.0
	 *
	 * @version 1.0.0 Initial version
	 */
	public static function getMediaPath($mediaUri, $cssMediaType = null)
	{
		if (is_array($mediaUri) || null === $mediaUri || empty($mediaUri)) {
			return false;
		}

		$urlData = parse_url($mediaUri);
		if (!is_array($urlData)) {
			return false;
		}

		if (!array_key_exists('host', $urlData)) {
			$mediaUri = '/'.ltrim(str_replace(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, _PS_ROOT_DIR_), __PS_BASE_URI__, $mediaUri), '/\\');
			// remove PS_BASE_URI on _PS_ROOT_DIR_ for the following
			$fileUri = _PS_ROOT_DIR_.Tools::str_replace_once(__PS_BASE_URI__, DIRECTORY_SEPARATOR, $mediaUri);

			if (!@filemtime($fileUri) || 0 === @filesize($fileUri)) {
				return false;
			}

			$mediaUri = str_replace('//', '/', $mediaUri);
		}

		if ($cssMediaType) {
			return [$mediaUri => $cssMediaType];
		}

		return $mediaUri;
	}

	/**
	 * Get the webpack chunks for a given entry name.
	 *
	 * @param string $entry Entry name
	 *
	 * @return array Array with chunk files, should be loaded in the given order
	 *
	 * @since 3.4.0
	 */
	public static function getWebpackChunks($entry)
	{
		static $manifest = null;
		if (!$manifest) {
			$manifest = [];
			foreach (include(_PS_MODULE_DIR_.'mollie/views/js/dist/manifest.php') as $chunk) {
				$manifest[$chunk['name']] = array_map(function ($chunk) {
					return UrlPathUtility::getMediaPath(_PS_MODULE_DIR_."mollie/views/js/dist/{$chunk}");
				}, $chunk['files']);
			}
		}

		return isset($manifest[$entry]) ? $manifest[$entry] : [];
	}
}
