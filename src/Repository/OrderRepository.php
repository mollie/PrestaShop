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
use Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

final class OrderRepository extends AbstractRepository implements OrderRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(Order::class);
    }

    /**
     * @param int $id_cart
     *
     * @return \ObjectModel|null
     *
     * @throws \PrestaShopException
     */
    public function findOneByCartId($id_cart)
    {
        return $this->findOneBy(['id_cart' => (int) $id_cart]);
    }

    /**
     * @param int $id_cart
     *
     * @return \PrestaShopCollection
     *
     * @throws \PrestaShopException
     */
    public function findAllByCartId($id_cart)
    {
        return $this->findAllBy(['id_cart' => (int) $id_cart]);
    }
}
