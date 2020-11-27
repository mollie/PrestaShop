<?php

namespace MolliePrefix;

use MolliePrefix\Symfony\Component\Console\Command\Command;
class FooSameCaseUppercaseCommand extends \MolliePrefix\Symfony\Component\Console\Command\Command
{
    protected function configure()
    {
        $this->setName('foo:BAR')->setDescription('foo:BAR command');
    }
}
\class_alias('MolliePrefix\\FooSameCaseUppercaseCommand', 'MolliePrefix\\FooSameCaseUppercaseCommand', \false);
