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

if (!defined('_PS_VERSION_')) {
    exit;
}

class ModuleRepository
{
    public function getModuleDatabaseVersion($moduleName)
    {
        $sql = new DbQuery();
        $sql->select('version');
        $sql->from('module');
        $sql->where('`name` = "' . pSQL($moduleName) . '"');

        return Db::getInstance()->getValue($sql);
    }
}
