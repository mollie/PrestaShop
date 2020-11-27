<?php

namespace MolliePrefix;

use MolliePrefix\Symfony\Component\Console\Command\Command;
use MolliePrefix\Symfony\Component\Console\Input\InputInterface;
use MolliePrefix\Symfony\Component\Console\Output\OutputInterface;
class FooSubnamespaced2Command extends \MolliePrefix\Symfony\Component\Console\Command\Command
{
    public $input;
    public $output;
    protected function configure()
    {
        $this->setName('foo:go:bret')->setDescription('The foo:bar:go command')->setAliases(['foobargo']);
    }
    protected function execute(\MolliePrefix\Symfony\Component\Console\Input\InputInterface $input, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
    }
}
\class_alias('MolliePrefix\\FooSubnamespaced2Command', 'MolliePrefix\\FooSubnamespaced2Command', \false);
