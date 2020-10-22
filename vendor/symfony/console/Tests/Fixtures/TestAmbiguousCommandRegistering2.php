<?php

namespace MolliePrefix;

use MolliePrefix\Symfony\Component\Console\Command\Command;
use MolliePrefix\Symfony\Component\Console\Input\InputInterface;
use MolliePrefix\Symfony\Component\Console\Output\OutputInterface;
class TestAmbiguousCommandRegistering2 extends \MolliePrefix\Symfony\Component\Console\Command\Command
{
    protected function configure()
    {
        $this->setName('test-ambiguous2')->setDescription('The test-ambiguous2 command');
    }
    protected function execute(\MolliePrefix\Symfony\Component\Console\Input\InputInterface $input, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $output->write('test-ambiguous2');
    }
}
\class_alias('MolliePrefix\\TestAmbiguousCommandRegistering2', 'MolliePrefix\\TestAmbiguousCommandRegistering2', \false);
