<?php

namespace Mollie\Provider;

interface UpdateMessageProviderInterface
{
	/**
	 * @param string $url
	 * @param mixed $addons
	 *
	 * @return string
	 *
	 * @throws \SmartyException
	 */
	public function getUpdateMessageFromOutsideUrl($url, $addons);
}
