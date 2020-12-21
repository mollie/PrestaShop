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

use Tools;

class UrlPathUtility
{
	/**
	 * @param string $mediaUri
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
		/* @phpstan-ignore-next-line */
		if (is_array($mediaUri) || null === $mediaUri || empty($mediaUri)) {
			return false;
		}

		$urlData = parse_url($mediaUri);
		if (!is_array($urlData)) {
			return false;
		}

		if (!array_key_exists('host', $urlData)) {
			$mediaUri = '/' . ltrim(str_replace(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, _PS_ROOT_DIR_), __PS_BASE_URI__, $mediaUri), '/\\');
			// remove PS_BASE_URI on _PS_ROOT_DIR_ for the following
			$fileUri = _PS_ROOT_DIR_ . Tools::str_replace_once(__PS_BASE_URI__, DIRECTORY_SEPARATOR, $mediaUri);

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
			foreach (include (_PS_MODULE_DIR_ . 'mollie/views/js/dist/manifest.php') as $chunk) {
				$manifest[$chunk['name']] = array_map(function ($chunk) {
					return UrlPathUtility::getMediaPath(_PS_MODULE_DIR_ . "mollie/views/js/dist/{$chunk}");
				}, $chunk['files']);
			}
		}

		return isset($manifest[$entry]) ? $manifest[$entry] : [];
	}
}
