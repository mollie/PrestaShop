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
use Context;
use Db;

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

    public function getProductIdFromName(string $name): ?int
    {
        $idLang = (int)Context::getContext()->language->id;

        $idProduct = Db::getInstance()->getValue('
            SELECT p.id_product
            FROM '._DB_PREFIX_.'product p
            INNER JOIN '._DB_PREFIX_.'product_lang pl
                ON (p.id_product = pl.id_product)
            WHERE pl.name = "'.pSQL($name).'"
              AND pl.id_lang = '.(int)$idLang.'
        ');

        return (int) ($idProduct ?: null);
    }
}
