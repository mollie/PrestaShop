<?php

namespace MolliePrefix;

use MolliePrefix\Symfony\Component\Console\Command\Command;
class Foo4Command extends \MolliePrefix\Symfony\Component\Console\Command\Command
{
    protected function configure()
    {
        $this->setName('foo3:bar:toh');
    }
}
\class_alias('MolliePrefix\\Foo4Command', 'MolliePrefix\\Foo4Command', \false);
