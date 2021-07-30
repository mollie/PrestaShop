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
