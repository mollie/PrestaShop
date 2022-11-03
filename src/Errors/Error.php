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

namespace Mollie\Errors;

class Error implements \JsonSerializable
{
    /** @var int|null */
    private $code;

    /** @var string|null */
    private $message;

    public function __construct($code, $message)
    {
        $this->message = $message;
        $this->code = $code;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function jsonSerialize()
    {
        $json = [];
        $json['code'] = $this->code;
        $json['message'] = $this->message;

        return array_filter($json, function ($val) {
            return $val !== null;
        });
    }
}
