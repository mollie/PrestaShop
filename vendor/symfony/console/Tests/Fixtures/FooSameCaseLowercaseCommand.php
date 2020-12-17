<?php

namespace MolliePrefix;

use MolliePrefix\Symfony\Component\Console\Command\Command;
class FooSameCaseLowercaseCommand extends \MolliePrefix\Symfony\Component\Console\Command\Command
{
    protected function configure()
    {
        $this->setName('foo:bar')->setDescription('foo:bar command');
    }
}
\class_alias('MolliePrefix\\FooSameCaseLowercaseCommand', 'FooSameCaseLowercaseCommand', \false);
