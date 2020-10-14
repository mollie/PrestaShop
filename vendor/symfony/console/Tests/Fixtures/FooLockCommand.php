<?php

namespace MolliePrefix;

use MolliePrefix\Symfony\Component\Console\Command\Command;
use MolliePrefix\Symfony\Component\Console\Command\LockableTrait;
use MolliePrefix\Symfony\Component\Console\Input\InputInterface;
use MolliePrefix\Symfony\Component\Console\Output\OutputInterface;
class FooLockCommand extends \MolliePrefix\Symfony\Component\Console\Command\Command
{
    use LockableTrait;
    protected function configure()
    {
        $this->setName('foo:lock');
    }
    protected function execute(\MolliePrefix\Symfony\Component\Console\Input\InputInterface $input, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface $output)
    {
        if (!$this->lock()) {
            return 1;
        }
        $this->release();
        return 2;
    }
}
\class_alias('MolliePrefix\\FooLockCommand', 'MolliePrefix\\FooLockCommand', \false);
