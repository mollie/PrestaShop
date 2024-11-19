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

use Mollie\Shared\Infrastructure\Repository\ReadOnlyRepositoryInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

interface ProductRepositoryInterface extends ReadOnlyRepositoryInterface
{
    public function getCombinationImageById(int $productAttributeId, int $langId): ?array;

    public function getCover(int $productId, \Context $context = null): array;
}
