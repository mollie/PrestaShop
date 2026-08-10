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

use Mollie\Adapter\LegacyContext;
use Mollie\Config\Config;
use MolPaymentMethod;
use PrestaShop\Decimal\Number;

if (!defined('_PS_VERSION_')) {
    exit;
}

/** Validator to keep Billink within the consumer purchase conditions Mollie accepts */
class BillinkPaymentMethodRestrictionValidator implements PaymentMethodRestrictionValidatorInterface
{
    /** @var LegacyContext */
    private $context;

    public function __construct(
        LegacyContext $context
    ) {
        $this->context = $context;
    }

    /**
     * {@inheritDoc}
     */
    public function isValid(MolPaymentMethod $paymentMethod): bool
    {
        if (Config::BILLINK_CURRENCY !== $this->context->getCurrencyIsoCode()) {
            return false;
        }

        if (!empty($this->context->getInvoiceCompany())) {
            return false;
        }

        $cartTotal = new Number((string) $this->context->getCart()->getOrderTotal());
        $maximumAmount = new Number((string) Config::BILLINK_B2C_MAXIMUM_AMOUNT);

        if ($maximumAmount->isLowerThan($cartTotal)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(MolPaymentMethod $paymentMethod): bool
    {
        return Config::BILLINK === $paymentMethod->getPaymentMethodName();
    }
}
