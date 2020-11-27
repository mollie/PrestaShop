<?php

namespace MolliePrefix\PrestaShop\CodingStandards\Command;

use MolliePrefix\Symfony\Component\Console\Command\Command;
use MolliePrefix\Symfony\Component\Console\Input\InputInterface;
use MolliePrefix\Symfony\Component\Console\Output\OutputInterface;
use MolliePrefix\Symfony\Component\Console\Question\ConfirmationQuestion;
use MolliePrefix\Symfony\Component\Filesystem\Filesystem;
abstract class AbstractCommand extends \MolliePrefix\Symfony\Component\Console\Command\Command
{
    /**
     * Copy file, check if file exists.
     * If yes, ask for overwrite
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $source
     * @param string $destination
     */
    protected function copyFile(\MolliePrefix\Symfony\Component\Console\Input\InputInterface $input, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface $output, $source, $destination)
    {
        $fs = new \MolliePrefix\Symfony\Component\Filesystem\Filesystem();
        if ($fs->exists($destination) && !$this->askForOverwrite($input, $output, $source, $destination)) {
            return;
        }
        $fs->copy($source, $destination);
        $output->writeln(\sprintf('File "%s" copied to "%s"', \basename($source), $destination));
    }
    /**
     * Ask for overwrite
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $source
     * @param string $destination
     * @param string $message
     * @param bool $default
     *
     * @return bool
     */
    protected function askForOverwrite(\MolliePrefix\Symfony\Component\Console\Input\InputInterface $input, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface $output, $source, $destination, $message = null, $default = \false)
    {
        if (null === $message) {
            $availableOptionsText = $default ? '[Y/n]' : '[y/N]';
            $message = \sprintf('%s already exists in destination folder %s. Overwrite? %s ', \pathinfo($source, \PATHINFO_BASENAME), \pathinfo(\realpath($destination), \PATHINFO_DIRNAME), $availableOptionsText);
        }
        $helper = $this->getHelper('question');
        $overwriteQuestion = new \MolliePrefix\Symfony\Component\Console\Question\ConfirmationQuestion($message, $default);
        return $helper->ask($input, $output, $overwriteQuestion);
    }
}
