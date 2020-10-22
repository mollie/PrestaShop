<?php

namespace MolliePrefix;

use MolliePrefix\Symfony\Component\Console\Command\Command;
use MolliePrefix\Symfony\Component\Console\Input\InputInterface;
use MolliePrefix\Symfony\Component\Console\Output\OutputInterface;
class TestAmbiguousCommandRegistering extends \MolliePrefix\Symfony\Component\Console\Command\Command
{
    protected function configure()
    {
        $this->setName('test-ambiguous')->setDescription('The test-ambiguous command')->setAliases(['test']);
    }
    protected function execute(\MolliePrefix\Symfony\Component\Console\Input\InputInterface $input, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $output->write('test-ambiguous');
    }
}
\class_alias('MolliePrefix\\TestAmbiguousCommandRegistering', 'MolliePrefix\\TestAmbiguousCommandRegistering', \false);
