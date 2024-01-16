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

namespace Mollie\Utility\Decoder;

if (!defined('_PS_VERSION_')) {
    exit;
}

class JsonDecoder implements DecoderInterface
{
    /**
     * @param string $encodedElement
     * @param array $params
     *
     * @return mixed
     */
    public function decode($encodedElement, $params = [])
    {
        return json_decode($encodedElement, ...$params);
    }
}
