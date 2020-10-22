<?php

namespace MolliePrefix;

use MolliePrefix\Symfony\Component\Console\Command\Command;
use MolliePrefix\Symfony\Component\Console\Input\InputInterface;
use MolliePrefix\Symfony\Component\Console\Output\OutputInterface;
class Foo2Command extends \MolliePrefix\Symfony\Component\Console\Command\Command
{
    protected function configure()
    {
        $this->setName('foo1:bar')->setDescription('The foo1:bar command')->setAliases(['afoobar2']);
    }
    protected function execute(\MolliePrefix\Symfony\Component\Console\Input\InputInterface $input, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface $output)
    {
    }
}
\class_alias('MolliePrefix\\Foo2Command', 'MolliePrefix\\Foo2Command', \false);
