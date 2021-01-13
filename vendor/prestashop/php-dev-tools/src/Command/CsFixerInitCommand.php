<?php

namespace MolliePrefix\PrestaShop\CodingStandards\Command;

use MolliePrefix\Symfony\Component\Console\Input\InputInterface;
use MolliePrefix\Symfony\Component\Console\Input\InputOption;
use MolliePrefix\Symfony\Component\Console\Output\OutputInterface;
use MolliePrefix\Symfony\Component\Filesystem\Filesystem;
class CsFixerInitCommand extends \MolliePrefix\PrestaShop\CodingStandards\Command\AbstractCommand
{
    protected function configure()
    {
        $this->setName('cs-fixer:init')->setDescription('Initialize Cs Fixer environement')->addOption('dest', null, \MolliePrefix\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Where the configuration will be stored', '.');
    }
    protected function execute(\MolliePrefix\Symfony\Component\Console\Input\InputInterface $input, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $fs = new \MolliePrefix\Symfony\Component\Filesystem\Filesystem();
        $directory = __DIR__ . '/../../templates/cs-fixer/';
        $destination = $input->getOption('dest');
        foreach (['php_cs.dist', 'prettyci.composer.json'] as $template) {
            $this->copyFile($input, $output, $directory . $template, $destination . '/.' . $template);
        }
        return 0;
    }
}
