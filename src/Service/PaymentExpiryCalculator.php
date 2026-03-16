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

namespace Mollie\Service;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Api\Types\PaymentMethod;
use Mollie\Config\Config;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentExpiryCalculator
{
    /** @var ConfigurationAdapter */
    private $configuration;

    public function __construct(ConfigurationAdapter $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Calculate due date for bank transfer payments (Payments API).
     * Returns date-only format as required by Mollie API dueDate field.
     *
     * @param string $methodId Mollie payment method ID
     *
     * @return ?string date in Y-m-d format or null if not applicable
     */
    public function calculateDueDate(string $methodId): ?string
    {
        $dueDateTime = $this->getDueDateTime($methodId);

        if (!$dueDateTime) {
            return null;
        }

        return $dueDateTime->format('Y-m-d');
    }

    /**
     * Calculate expiry datetime for bank transfer payments (Orders API).
     *
     * @param string $methodId Mollie payment method ID
     *
     * @return ?string ISO 8601 datetime or null if not applicable
     */
    public function calculateExpiresAt(string $methodId): ?string
    {
        $dueDateTime = $this->getDueDateTime($methodId);

        if (!$dueDateTime) {
            return null;
        }

        return $dueDateTime->format('c');
    }

    /**
     * @return ?\DateTime
     */
    private function getDueDateTime(string $methodId): ?\DateTime
    {
        if (PaymentMethod::BANKTRANSFER !== $methodId) {
            return null;
        }

        $dueDays = (int) $this->configuration->get(
            Config::MOLLIE_BANKTRANSFER_DUE_DAYS
        );

        if ($dueDays < 1 || $dueDays > 90) {
            $dueDays = Config::MOLLIE_BANKTRANSFER_DUE_DAYS_DEFAULT;
        }

        $dueDateTime = new \DateTime();
        $dueDateTime->modify("+{$dueDays} days");

        return $dueDateTime;
    }
}
