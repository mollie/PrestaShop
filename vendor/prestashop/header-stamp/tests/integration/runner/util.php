<?php

namespace MolliePrefix;

/**
 * @param string $moduleName
 * @param string[] $list
 */
function printErrorsList($moduleName, $list)
{
    echo "\33[31m";
    $message = \sprintf('Test failed for module %s, got differences between expected folder and workspace folder :', $moduleName);
    echo $message . \PHP_EOL;
    foreach ($list as $item) {
        echo ' - ' . $item . \PHP_EOL;
    }
    echo "\33[37m";
}
/**
 * @param string $message
 */
function printErrorMessage($message)
{
    echo "\33[31m";
    echo $message;
    echo "\33[37m";
}
/**
 * @param string $message
 */
function printSuccessMessage($message)
{
    echo "\33[32m";
    echo $message;
    echo "\33[37m";
}
/**
 * @return \Symfony\Component\Console\Application
 */
function buildTestApplication()
{
    $application = new \MolliePrefix\Symfony\Component\Console\Application('header-stamp', '9.9.9');
    $command = new \MolliePrefix\PrestaShop\HeaderStamp\Command\UpdateLicensesCommand();
    $application->add($command);
    $application->setDefaultCommand($command->getName());
    $application->setAutoExit(\false);
    return $application;
}
