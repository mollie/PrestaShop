<?php

declare(strict_types=1);

namespace Mollie\Subscription\Validator;

use Combination;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\ProductAttributeAdapter;
use Mollie\Subscription\Config\Config;
use Mollie\Subscription\Repository\CombinationRepository;
use Mollie\Subscription\Repository\ProductCombinationRepository;

class SubscriptionProductValidator
{
    /** @var ConfigurationAdapter */
    private $configuration;

    /** @var ProductCombinationRepository */
    private $combinationRepository;

    /** @var CombinationRepository */
    private $combination;
    /** @var ProductAttributeAdapter */
    private $productAttributeAdapter;

    public function __construct(
        ConfigurationAdapter $configuration,
        ProductCombinationRepository $combinationRepository,
        CombinationRepository $combination,
        ProductAttributeAdapter $productAttributeAdapter
    ) {
        $this->configuration = $configuration;
        $this->combinationRepository = $combinationRepository;
        $this->combination = $combination;
        $this->productAttributeAdapter = $productAttributeAdapter;
    }

    /**
     * Validate if given product combination is subscription product
     */
    public function validate(int $productAttributeId): bool
    {
        $combination = $this->combination->getById($productAttributeId);
        $attributeIds = $this->combinationRepository->getIds((int) $combination->id);

        foreach ($attributeIds as $attributeId) {
            if ($this->isSubscriptionAttribute((int) $attributeId['id_attribute'])) {
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
