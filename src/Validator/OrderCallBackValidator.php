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

use Mollie;
use Mollie\Adapter\Customer;
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

    public function __construct(Customer $customer, Mollie $module)
    {
        $this->module = $module;
        $this->customer = $customer;
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
                $this->customer->getCustomer()->id,
                $cartId,
                $this->module->name
            );
    }
}
