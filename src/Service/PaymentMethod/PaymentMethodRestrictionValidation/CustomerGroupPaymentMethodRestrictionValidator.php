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
use Mollie\Logger\LoggerInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use MolPaymentMethod;
use Validate;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CustomerGroupPaymentMethodRestrictionValidator implements PaymentMethodRestrictionValidatorInterface
{
    const FILE_NAME = 'CustomerGroupPaymentMethodRestrictionValidator';

    /** @var PaymentMethodRepositoryInterface */
    private $paymentMethodRepository;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        LoggerInterface $logger
    ) {
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function isValid(MolPaymentMethod $paymentMethod): bool
    {
        $customer = Context::getContext()->customer;

        if (!Validate::isLoadedObject($customer)) {
            $this->logger->debug(sprintf('%s - Customer cannot be loaded', self::FILE_NAME));

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
