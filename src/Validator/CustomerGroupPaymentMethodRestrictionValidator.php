<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 */

namespace Mollie\Validator;

use Context;
use Mollie\Repository\PaymentMethodRepository;

class CustomerGroupPaymentMethodRestrictionValidator implements PaymentMethodRestrictionValidatorInterface
{
    /**
     * @var PaymentMethodRepository
     */
    private $paymentMethodRepository;

    public function __construct(PaymentMethodRepository $paymentMethodRepository)
    {
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function isValid($paymentMethodId)
    {
        $customer = Context::getContext()->customer;
        if (!$customer) {
            return true;
        }

        $customerGroups = $customer->getGroups();
        if (empty($customerGroups)) {
            return true;
        }

        $restrictedGroups = $this->paymentMethodRepository->getCustomerGroupsForPaymentMethod($paymentMethodId);
        
        // If no groups are restricted, payment method is available to all
        if (empty($restrictedGroups)) {
            return true;
        }

        // Check if customer belongs to any of the allowed groups
        foreach ($customerGroups as $customerGroup) {
            if (in_array($customerGroup, $restrictedGroups)) {
                return true;
            }
        }

        return false;
    }
} 