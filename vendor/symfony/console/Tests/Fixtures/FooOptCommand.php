<?php

namespace MolliePrefix;

use MolliePrefix\Symfony\Component\Console\Command\Command;
use MolliePrefix\Symfony\Component\Console\Input\InputInterface;
use MolliePrefix\Symfony\Component\Console\Input\InputOption;
use MolliePrefix\Symfony\Component\Console\Output\OutputInterface;
class FooOptCommand extends \MolliePrefix\Symfony\Component\Console\Command\Command
{
    public $input;
    public $output;
    protected function configure()
    {
        $this->setName('foo:bar')->setDescription('The foo:bar command')->setAliases(['afoobar'])->addOption('fooopt', 'fo', \MolliePrefix\Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'fooopt description');
    }
    protected function interact(\MolliePrefix\Symfony\Component\Console\Input\InputInterface $input, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $output->writeln('interact called');
    }
    protected function execute(\MolliePrefix\Symfony\Component\Console\Input\InputInterface $input, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $output->writeln('called');
        $output->writeln($this->input->getOption('fooopt'));
    }
}
\class_alias('MolliePrefix\\FooOptCommand', 'MolliePrefix\\FooOptCommand', \false);
