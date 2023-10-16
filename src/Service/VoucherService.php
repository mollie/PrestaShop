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
use Mollie\Config\Config;

class VoucherService
{
    /**
     * @var ConfigurationAdapter
     */
    private $configuration;

    public function __construct(
        ConfigurationAdapter $configuration
    ) {
        $this->configuration = $configuration;
    }

    public function getVoucherCategory(array $cartItem, $selectedVoucherCategory)
    {
        switch ($selectedVoucherCategory) {
            case Config::MOLLIE_VOUCHER_CATEGORY_MEAL:
            case Config::MOLLIE_VOUCHER_CATEGORY_GIFT:
            case Config::MOLLIE_VOUCHER_CATEGORY_ECO:
                $productCategory = $this->getProductCategory($cartItem);
                if ($productCategory) {
                    return $productCategory;
                }

                return $selectedVoucherCategory;
            case Config::MOLLIE_VOUCHER_CATEGORY_NULL:
            default:
                return $this->getProductCategory($cartItem);
        }
    }

    public function getProductCategory(array $cartItem): string
    {
        if (!isset($cartItem['features'])) {
            return '';
        }

        $idFeatureValue = false;

        foreach ($cartItem['features'] as $feature) {
            if (!$this->isVoucherFeature((int) $feature['id_feature'])) {
                continue;
            }

            $idFeatureValue = (int) $feature['id_feature_value'];
        }

        if (!$idFeatureValue) {
            return '';
        }

        return $this->getVoucherCategoryByFeatureValueId($idFeatureValue);
    }

    private function isVoucherFeature(int $featureId): bool
    {
        return (int) $this->configuration->get(Config::MOLLIE_VOUCHER_FEATURE_ID) === $featureId;
    }

    private function getVoucherCategoryByFeatureValueId(int $idFeatureValue): string
    {
        foreach (Config::MOLLIE_VOUCHER_CATEGORIES as $key => $categoryName) {
            if ((int) $this->configuration->get(Config::MOLLIE_VOUCHER_FEATURE . $key) === $idFeatureValue) {
                return $key;
            }
        }

        return '';
    }
}
