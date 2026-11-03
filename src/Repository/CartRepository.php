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

use Db;
use DbQuery;
use Mollie\Shared\Infrastructure\Repository\AbstractRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CartRepository extends AbstractRepository implements CartRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(\Cart::class);
    }

    public function getTemporaryApplePayLineAddressIds(int $cartId): array
    {
        $query = new DbQuery();
        $query->select('DISTINCT cp.`id_address_delivery`');
        $query->from('cart_product', 'cp');
        $query->innerJoin('address', 'a', 'a.`id_address` = cp.`id_address_delivery`');
        $query->where('cp.`id_cart` = ' . $cartId);
        $query->where('a.`alias` = \'applePay\'');
        $query->where('a.`deleted` = 1');

        return array_map('intval', array_column(Db::getInstance()->executeS($query) ?: [], 'id_address_delivery'));
    }

    public function moveLinesToAddress(int $cartId, int $fromAddressId, int $toAddressId): void
    {
        $where = '`id_cart` = ' . $cartId . ' AND `id_address_delivery` = ' . $fromAddressId;

        Db::getInstance()->update('cart_product', ['id_address_delivery' => $toAddressId], $where);
        Db::getInstance()->update('customization', ['id_address_delivery' => $toAddressId], $where);
    }
}
