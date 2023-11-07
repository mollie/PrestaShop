<?php

namespace Mollie\Install\Install\Command;

class MollieCartTableInstallCommand implements InstallCommandInterface
{
    public function getName(): string
    {
        return \MollieCart::$definition['table'];
    }

    public function getCommand(): string
    {
        return 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . pSQL(\MollieCart::$definition['table']) . '` (
                `id_mollie_cart` INT(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_cart` INT(10) NOT NULL,
                `id_shop` INT(10) NOT NULL,
            PRIMARY KEY(`id_mollie_cart`, `id_cart`, `id_shop`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
    }
}
