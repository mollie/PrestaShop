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

declare(strict_types=1);

namespace Mollie\Provider\PaymentMethod;

use Mollie\Config\Config;
use Mollie\Logger\LoggerInterface;
use Mollie\Provider\CreditCardLogoProvider;
use Mollie\Service\CountryService;
use Mollie\Utility\ExceptionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentMethodConfigProvider
{
    /** @var CountryService */
    private $countryService;

    /** @var CreditCardLogoProvider */
    private $creditCardLogoProvider;

    /** @var LoggerInterface */
    private $logger;

    /** @var \Context */
    private $context;

    public function __construct(
        CountryService $countryService,
        CreditCardLogoProvider $creditCardLogoProvider,
        LoggerInterface $logger,
        \Context $context
    ) {
        $this->countryService = $countryService;
        $this->creditCardLogoProvider = $creditCardLogoProvider;
        $this->logger = $logger;
        $this->context = $context;
    }

    /**
     * Get all configuration data for frontend
     *
     * @return array Configuration data
     */
    public function getConfigurationData(): array
    {
        return [
            'countries' => $this->countryService->getActiveCountriesList(),
            'taxRulesGroups' => $this->getTaxRulesGroups(),
            'customerGroups' => $this->getCustomerGroups(),
            'onlyOrderMethods' => Config::ORDER_API_ONLY_METHODS,
            'onlyPaymentsMethods' => Config::PAYMENT_API_ONLY_METHODS,
        ];
    }

    /**
     * Get tax rules groups for select options
     *
     * @return array Tax rules groups
     */
    public function getTaxRulesGroups(): array
    {
        $taxRulesGroups = [];

        try {
            $sql = 'SELECT id_tax_rules_group, name
                    FROM ' . _DB_PREFIX_ . 'tax_rules_group
                    WHERE active = 1 AND deleted = 0';
            $groups = \Db::getInstance()->executeS($sql) ?: [];

            foreach ($groups as $group) {
                $taxRulesGroups[] = [
                    'value' => $group['id_tax_rules_group'],
                    'label' => $group['name'],
                ];
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to get tax rules groups', [
                'exception' => ExceptionUtility::getExceptions($e),
            ]);
        }

        return $taxRulesGroups;
    }

    /**
     * Get customer groups for select options
     *
     * @return array Customer groups
     */
    public function getCustomerGroups(): array
    {
        $customerGroups = [];

        try {
            $groups = \Group::getGroups($this->context->language->id);

            foreach ($groups as $group) {
                $customerGroups[] = [
                    'value' => $group['id_group'],
                    'label' => $group['name'],
                ];
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to get customer groups', [
                'exception' => ExceptionUtility::getExceptions($e),
            ]);
        }

        return $customerGroups;
    }

    /**
     * Get payment fee type based on surcharge configuration
     *
     * @param \MolPaymentMethod $methodObj Payment method object
     *
     * @return string Fee type (none, fixed, percentage, combined)
     */
    public function getPaymentFeeType($methodObj): string
    {
        $surcharge = isset($methodObj->surcharge) ? (int) $methodObj->surcharge : 0;

        switch ($surcharge) {
            case 1:
                return 'fixed';
            case 2:
                return 'percentage';
            case 3:
                return 'combined';
            default:
                return 'none';
        }
    }

    /**
     * Get custom logo URL if it exists
     *
     * @return string|null Logo URL or null
     */
    public function getCustomLogoUrl(): ?string
    {
        try {
            if ($this->creditCardLogoProvider->logoExists()) {
                return $this->creditCardLogoProvider->getLogoPathUri() . '?' . time();
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to get custom logo URL', [
                'exception' => ExceptionUtility::getExceptions($e),
            ]);
        }

        return null;
    }

    /**
     * Calculate fixed fee tax incl from tax excl + tax rules group
     *
     * @param \MolPaymentMethod $methodObj Payment method object
     *
     * @return string Fixed fee with tax included
     */
    public function calculateFixedFeeTaxIncl($methodObj): string
    {
        if (!isset($methodObj->surcharge_fixed_amount_tax_excl) || $methodObj->surcharge_fixed_amount_tax_excl <= 0) {
            return '0.00';
        }

        $taxExcl = (float) $methodObj->surcharge_fixed_amount_tax_excl;
        $taxRulesGroupId = (string) $methodObj->tax_rules_group_id;

        if (!$taxRulesGroupId) {
            return number_format($taxExcl, 2, '.', '');
        }

        try {
            // Use PrestaShop's tax manager
            $address = new \Address();
            if (isset($this->context->cart->id_address_delivery)) {
                $address = new \Address((int) $this->context->cart->id_address_delivery);
            }

            $taxManager = \TaxManagerFactory::getManager($address, $taxRulesGroupId);
            $taxCalculator = $taxManager->getTaxCalculator();

            $taxIncl = $taxCalculator->addTaxes($taxExcl);

            return number_format($taxIncl, 2, '.', '');
        } catch (\Exception $e) {
            $this->logger->error('Failed to calculate fixed fee tax incl', [
                'exception' => ExceptionUtility::getExceptions($e),
                'tax_excl' => $taxExcl,
                'tax_rules_group_id' => $taxRulesGroupId,
            ]);
        }

        return number_format($taxExcl, 2, '.', '');
    }
}
