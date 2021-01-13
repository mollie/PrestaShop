<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Service;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Repository\AttributeRepository;

class VoucherService
{
	/**
	 * @var AttributeRepository
	 */
	private $attributeRepository;

	/**
	 * @var ConfigurationAdapter
	 */
	private $configuration;

	public function __construct(
		AttributeRepository $attributeRepository,
		ConfigurationAdapter $configuration
	) {
		$this->attributeRepository = $attributeRepository;
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

	public function getProductCategory(array $cartItem)
	{
		if (!isset($cartItem['features'])) {
			return '';
		}
		$idFeatureValue = false;
		foreach ($cartItem['features'] as $feature) {
			if (!$this->isVoucherFeature($feature['id_feature'])) {
				continue;
			}
			$idFeatureValue = $feature['id_feature_value'];
		}

		if (!$idFeatureValue) {
			return '';
		}

		return $this->getVoucherCategoryByFeatureValueId($idFeatureValue);
	}

	private function isVoucherFeature($featureId)
	{
		return (int) $this->configuration->get(Config::MOLLIE_VOUCHER_FEATURE_ID) === (int) $featureId;
	}

	private function getVoucherCategoryByFeatureValueId($idFeatureValue)
	{
		foreach (Config::MOLLIE_VOUCHER_CATEGORIES as $key => $categoryName) {
			if ($this->configuration->get(Config::MOLLIE_VOUCHER_FEATURE . $key) === $idFeatureValue) {
				return $key;
			}
		}

		return '';
	}
}
