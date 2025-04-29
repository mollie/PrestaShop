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

namespace Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidation;

use Context;
use Mollie\Repository\PaymentMethodRepository;
use MolPaymentMethod;

if (!defined('_PS_VERSION_')) {
    exit;
}

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
    public function isValid(MolPaymentMethod $paymentMethod): bool
    {
        $customer = Context::getContext()->customer;
        if (Validate::isObjectLoaded($customer)) {
            return true;
        }

        $customerGroups = $customer->getGroups();
        if (empty($customerGroups)) {
            return true;
        }

        $restrictedGroups = $this->paymentMethodRepository->getCustomerGroupsForPaymentMethod($paymentMethod->id);

        if (empty($restrictedGroups)) {
            return true;
        }

        foreach ($customerGroups as $customerGroup) {
            if (in_array($customerGroup, $restrictedGroups)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(MolPaymentMethod $paymentMethod): bool
    {
        return true;
    }
}
