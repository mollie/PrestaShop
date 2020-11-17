<?php

namespace MolliePrefix;

use MolliePrefix\Symfony\Component\Console\Command\Command;
class Foo6Command extends \MolliePrefix\Symfony\Component\Console\Command\Command
{
    protected function configure()
    {
        $this->setName('0foo:bar')->setDescription('0foo:bar command');
    }
}
\class_alias('MolliePrefix\\Foo6Command', 'Foo6Command', \false);
