<?php

namespace MolliePrefix\PrestaShop\CodingStandards\Command;

use MolliePrefix\Symfony\Component\Console\Input\InputInterface;
use MolliePrefix\Symfony\Component\Console\Input\InputOption;
use MolliePrefix\Symfony\Component\Console\Output\OutputInterface;
use MolliePrefix\Symfony\Component\Filesystem\Filesystem;
class PhpStanInitCommand extends \MolliePrefix\PrestaShop\CodingStandards\Command\AbstractCommand
{
    protected function configure()
    {
        $this->setName('phpstan:init')->setDescription('Initialize phpstan environement')->addOption('dest', null, \MolliePrefix\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Where the configuration will be stored', 'tests/phpstan');
    }
    protected function execute(\MolliePrefix\Symfony\Component\Console\Input\InputInterface $input, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $fs = new \MolliePrefix\Symfony\Component\Filesystem\Filesystem();
        $directory = __DIR__ . '/../../templates/phpstan/';
        $destination = $input->getOption('dest');
        foreach (['phpstan.neon'] as $template) {
            $this->copyFile($input, $output, $directory . $template, $destination . '/' . $template);
        }
    }
}
