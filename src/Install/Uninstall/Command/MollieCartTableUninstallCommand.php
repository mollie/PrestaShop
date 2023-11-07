<?php

namespace Mollie\Install\Uninstall\Command;

class MollieCartTableUninstallCommand implements UninstallCommandInterface
{
    public function getName(): string
    {
        return \MollieCart::$definition['table'];
    }

    public function getCommand(): string
    {
        return 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . pSQL(\MollieCart::$definition['table']) . '`;';
    }
}
