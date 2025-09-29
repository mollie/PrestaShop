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

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\Context;
use Mollie\Api\Types\PaymentMethod;
use Mollie\Repository\AddressFormatRepositoryInterface;
use Mollie\Repository\AddressRepositoryInterface;
use Mollie\Repository\CustomerRepositoryInterface;
use MolPaymentMethod;

if (!defined('_PS_VERSION_')) {
    exit;
}

class B2bPaymentMethodRestrictionValidator implements PaymentMethodRestrictionValidatorInterface
{
    /** @var Context */
    private $context;
    /** @var AddressRepositoryInterface */
    private $addressRepository;
    /** @var CustomerRepositoryInterface */
    private $customerRepository;
    /** @var ConfigurationAdapter */
    private $configuration;
    /** @var AddressFormatRepositoryInterface */
    private $addressFormatRepository;

    public function __construct(
        Context $context,
        AddressRepositoryInterface $addressRepository,
        CustomerRepositoryInterface $customerRepository,
        ConfigurationAdapter $configuration,
        AddressFormatRepositoryInterface $addressFormatRepository
    ) {
        $this->context = $context;
        $this->addressRepository = $addressRepository;
        $this->customerRepository = $customerRepository;
        $this->configuration = $configuration;
        $this->addressFormatRepository = $addressFormatRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function isValid(MolPaymentMethod $paymentMethod): bool
    {
        if (!$this->isB2bEnabled()) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(MolPaymentMethod $paymentMethod): bool
    {
        return $paymentMethod->getPaymentMethodName() === PaymentMethod::BILLIE;
    }

    private function isB2bEnabled(): bool
    {
        return (bool) (int) $this->configuration->get('PS_B2B_ENABLE');
    }
}
