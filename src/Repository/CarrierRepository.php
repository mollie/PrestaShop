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

if (!defined('_PS_VERSION_')) {
    exit;
}

class CarrierRepository extends AbstractRepository implements CarrierRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(\Carrier::class);
    }

    public function getCarriersForOrder(int $id_zone, array $groups = null, \Cart $cart = null, &$error = []): array
    {
        return \Carrier::getCarriersForOrder($id_zone, $groups, $cart, $error);
    }
}
