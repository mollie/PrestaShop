<?php

namespace MolliePrefix;

use MolliePrefix\Symfony\Component\Console\Command\Command;
class BarBucCommand extends \MolliePrefix\Symfony\Component\Console\Command\Command
{
    protected function configure()
    {
        $this->setName('bar:buc');
    }
}
\class_alias('MolliePrefix\\BarBucCommand', 'MolliePrefix\\BarBucCommand', \false);
