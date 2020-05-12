<?php

namespace Mollie\Repository;

use Db;
use DbQuery;

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