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

namespace Mollie\Provider;

use Mollie\Repository\TaxRepositoryInterface;
use Mollie\Repository\TaxRuleRepositoryInterface;
use Tax;
use TaxCalculator;

if (!defined('_PS_VERSION_')) {
    exit;
}

class TaxCalculatorProvider
{
    /** @var TaxRuleRepositoryInterface */
    private $taxRuleRepository;
    /** @var TaxRepositoryInterface */
    private $taxRepository;

    public function __construct(
        TaxRuleRepositoryInterface $taxRuleRepository,
        TaxRepositoryInterface $taxRepository
    ) {
        $this->taxRuleRepository = $taxRuleRepository;
        $this->taxRepository = $taxRepository;
    }

    public function getTaxCalculator(int $taxRulesGroupId, int $countryId, int $stateId): TaxCalculator
    {
        $taxRules = $this->taxRuleRepository->getTaxRule(
            $taxRulesGroupId,
            $countryId,
            $stateId
        );

        $taxes = [];
        $behavior = 0;
        $firstRow = true;

        foreach ($taxRules as $taxRule) {
            /** @var Tax|null $tax */
            $tax = $this->taxRepository->findOneBy([
                'id_tax' => $taxRule['id_tax'],
            ]);

            if (!$tax || !$tax->id) {
                continue;
            }

            $taxes[] = $tax;

            // the applied behavior correspond to the most specific rules
            if ($firstRow) {
                $behavior = (int) $taxRule['behavior'];
                $firstRow = false;
            }

            if ((int) $taxRule['behavior'] === 0) {
                break;
            }
        }

        return new TaxCalculator($taxes, $behavior);
    }
}
