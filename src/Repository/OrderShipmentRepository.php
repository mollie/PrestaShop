<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Repository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderShipmentRepository
{
    public function getShipmentInformation($table, $orderId)
    {
        $sql = new \DbQuery();
        $sql->select('`tracktrace`, `postcode`');
        $sql->from(bqSQL($table));
        $sql->where('`id_order` = "' . pSQL($orderId) . '"');

        return \Db::getInstance()->getRow($sql);
    }
}
