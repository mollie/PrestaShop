<?php

namespace MolliePrefix;

use MolliePrefix\Symfony\Component\Console\Command\Command;
use MolliePrefix\Symfony\Component\Console\Input\InputInterface;
use MolliePrefix\Symfony\Component\Console\Output\OutputInterface;
class Foo3Command extends \MolliePrefix\Symfony\Component\Console\Command\Command
{
    protected function configure()
    {
        $this->setName('foo3:bar')->setDescription('The foo3:bar command');
    }
    protected function execute(\MolliePrefix\Symfony\Component\Console\Input\InputInterface $input, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface $output)
    {
        try {
            try {
                throw new \Exception('First exception <p>this is html</p>');
            } catch (\Exception $e) {
                throw new \Exception('Second exception <comment>comment</comment>', 0, $e);
            }
        } catch (\Exception $e) {
            throw new \Exception('Third exception <fg=blue;bg=red>comment</>', 404, $e);
        }
    }
}
\class_alias('MolliePrefix\\Foo3Command', 'Foo3Command', \false);
