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

namespace Mollie\Verification\PaymentType;

use Mollie\Adapter\ToolsAdapter;
use Mollie\Provider\PaymentType\PaymentTypeIdentificationProviderInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CanBeRegularPaymentType implements PaymentTypeVerificationInterface
{
    /**
     * @var PaymentTypeIdentificationProviderInterface
     */
    private $regularPaymentTypeIdentification;

    /**
     * @var ToolsAdapter
     */
    private $toolsAdapter;

    public function __construct(
        ToolsAdapter $toolsAdapter,
        PaymentTypeIdentificationProviderInterface $regularPaymentTypeIdentification
    ) {
        $this->regularPaymentTypeIdentification = $regularPaymentTypeIdentification;
        $this->toolsAdapter = $toolsAdapter;
    }

    /**
     * {@inheritDoc}
     */
    public function verify($transactionId)
    {
        if (!$transactionId) {
            return false;
        }

        $regularPaymentTypeIdentification = $this->regularPaymentTypeIdentification->getRegularPaymentIdentification();

        if (!$regularPaymentTypeIdentification) {
            return false;
        }
        $length = $this->toolsAdapter->strlen($regularPaymentTypeIdentification);

        if (!$length) {
            return false;
        }

        if ($regularPaymentTypeIdentification !== $this->toolsAdapter->substr($transactionId, 0, $length)) {
            return false;
        }

        return true;
    }
}
