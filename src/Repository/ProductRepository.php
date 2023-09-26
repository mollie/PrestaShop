<?php

namespace Mollie\Repository;

class ProductRepository extends AbstractRepository implements ProductRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(\Product::class);
    }

    public function getCombinationImageById(int $productAttributeId, int $langId): ?array
    {
        $result = \Product::getCombinationImageById($productAttributeId, $langId);

        return empty($result) ? null : $result;
    }

    public function getCover(int $productId, \Context $context = null): array
    {
        return \Product::getCover($productId, $context);
    }
}
