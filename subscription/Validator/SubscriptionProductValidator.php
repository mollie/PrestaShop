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

namespace Mollie\Subscription\Validator;

use Combination;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\ProductAttributeAdapter;
use Mollie\Subscription\Config\Config;
use Mollie\Subscription\Repository\CombinationRepositoryInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SubscriptionProductValidator
{
    /** @var ConfigurationAdapter */
    private $configuration;
    /** @var CombinationRepositoryInterface */
    private $combinationRepository;
    /** @var ProductAttributeAdapter */
    private $productAttributeAdapter;

    public function __construct(
        ConfigurationAdapter $configuration,
        CombinationRepositoryInterface $combinationRepository,
        ProductAttributeAdapter $productAttributeAdapter
    ) {
        $this->configuration = $configuration;
        $this->combinationRepository = $combinationRepository;
        $this->productAttributeAdapter = $productAttributeAdapter;
    }

    /**
     * Validate if given product combination is subscription product
     */
    public function validate(int $productAttributeId): bool
    {
        /** @var \Combination|null $combination */
        $combination = $this->combinationRepository->findOneBy([
            'id_product_attribute' => $productAttributeId,
        ]);

        if (!$combination) {
            return false;
        }

        foreach ($combination->getWsProductOptionValues() as $attribute) {
            if ($this->isSubscriptionAttribute((int) $attribute['id'])) {
                return true;
            }
        }

        return false;
    }

    private function isSubscriptionAttribute(int $attributeId): bool
    {
        // need to add core because if we use Attribute then symfony attribute is used
        $attribute = $this->productAttributeAdapter->getProductAttribute($attributeId);

        if ($attributeId === (int) $this->configuration->get(Config::SUBSCRIPTION_ATTRIBUTE_NONE)) {
            return false;
        }

        return (int) $attribute->id_attribute_group === (int) $this->configuration->get(Config::SUBSCRIPTION_ATTRIBUTE_GROUP);
    }
}
