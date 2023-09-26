<?php

namespace Mollie\Repository;

interface ProductRepositoryInterface extends ReadOnlyRepositoryInterface
{
    public function getCombinationImageById(int $productAttributeId, int $langId): ?array;

    public function getCover(int $productId, \Context $context = null): array;
}
