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

namespace Mollie\Repository;

use Mollie\Shared\Infrastructure\Repository\AbstractRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

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
