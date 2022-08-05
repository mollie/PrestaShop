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

namespace Mollie\Validator;

use Context;
use Customer;
use Mollie;
use Mollie\Utility\SecureKeyUtility;

class OrderCallBackValidator
{
    /**
     * @var Customer
     */
    private $customer;

    /**
     * @var Mollie
     */
    private $module;

    public function __construct(Context $context, Mollie $module)
    {
        $this->customer = $context->customer;
        $this->module = $module;
    }

    public function validate($key, $cartId)
    {
        return $this->isSignatureMatches($key, $cartId);
    }

    /**
     * Checks If Signature Matches.
     *
     * @param string $key
     * @param int $cartId
     *
     * @return bool
     */
    public function isSignatureMatches($key, $cartId)
    {
        return $key === SecureKeyUtility::generateReturnKey(
                $this->customer->id,
                $cartId,
                $this->module->name
            );
    }
}
