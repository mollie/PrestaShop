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

class AddressRepository extends AbstractRepository implements AddressRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(\Address::class);
    }

    public function getZoneById(int $id_address_delivery): int
    {
        return \Address::getZoneById($id_address_delivery);
    }
}
