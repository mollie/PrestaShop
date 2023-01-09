<?php

declare(strict_types=1);

namespace Mollie\Subscription\Validator;

use Combination;
use Mollie\Subscription\Adapter\Configuration;
use Mollie\Subscription\Config\Config;
use Mollie\Subscription\Repository\ProductCombinationRepository;

class SubscriptionProduct
{
    /** @var Configuration */
    private $configuration;

    /** @var ProductCombinationRepository */
    private $combinationRepository;

    /** @var \MollieSubscription\Repository\Combination */
    private $combination;

    public function __construct(
        Configuration $configuration,
        ProductCombinationRepository $combinationRepository,
        \MollieSubscription\Repository\Combination $combination
    ) {
        $this->configuration = $configuration;
        $this->combinationRepository = $combinationRepository;
        $this->combination = $combination;
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
        $attribute = new \AttributeCore($attributeId);

        if ($attributeId === (int) $this->configuration->get(Config::SUBSCRIPTION_ATTRIBUTE_NONE)) {
            return false;
        }

        return (int) $attribute->id_attribute_group === (int) $this->configuration->get(Config::SUBSCRIPTION_ATTRIBUTE_GROUP);
    }
}
