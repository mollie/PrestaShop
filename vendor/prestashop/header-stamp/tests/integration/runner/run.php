#!/usr/bin/env php
<?php 
namespace MolliePrefix;

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/util.php';
require_once __DIR__ . '/FolderComparator.php';
use MolliePrefix\PrestaShop\HeaderStamp\Command\UpdateLicensesCommand;
use MolliePrefix\Symfony\Component\Console\Input\ArrayInput;
use MolliePrefix\Symfony\Component\Console\Output\BufferedOutput;
use MolliePrefix\Symfony\Component\Filesystem\Filesystem;
$modulesToTest = ['gsitemap', 'dashproducts', 'fakemodule'];
$workspaceID = 100;
$filesystem = new \MolliePrefix\Symfony\Component\Filesystem\Filesystem();
$folderComparator = new \MolliePrefix\FolderComparator();
$application = \MolliePrefix\buildTestApplication();
foreach ($modulesToTest as $moduleName) {
    ++$workspaceID;
    $moduleFolderpath = __DIR__ . '/../module-samples/' . $moduleName;
    $expectedModuleFolderpath = __DIR__ . '/../expected/' . $moduleName;
    $workspaceFolderpath = __DIR__ . '/../workspace/' . $workspaceID;
    // copy module into workspace
    $filesystem->mirror($moduleFolderpath, $workspaceFolderpath);
    // run UpdateLicensesCommand on workspace
    $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput(['command' => 'prestashop:licenses:update', '--license' => __DIR__ . '/../../../assets/afl.txt', '--target' => $workspaceFolderpath]);
    $output = new \MolliePrefix\Symfony\Component\Console\Output\BufferedOutput();
    $application->run($input, $output);
    // compare workspace with expected
    $check = $folderComparator->compareFolders($expectedModuleFolderpath, $workspaceFolderpath, '');
    $check2 = $folderComparator->compareFolders($workspaceFolderpath, $expectedModuleFolderpath, '');
    // empty workspace
    $filesystem->remove($workspaceFolderpath);
    if (!empty($check)) {
        \MolliePrefix\printErrorsList($moduleName, $check);
        exit(1);
    }
    if (!empty($check2)) {
        \MolliePrefix\printErrorsList($moduleName, $check2);
        exit(1);
    }
    \MolliePrefix\printSuccessMessage(' - module ' . $moduleName . ' processed successfully' . \PHP_EOL);
}
\MolliePrefix\printSuccessMessage('Integration tests run successfully' . \PHP_EOL);
exit(0);
