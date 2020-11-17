<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace MolliePrefix\PhpCsFixer\Console\Command;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\Documentation\DocumentationGenerator;
use MolliePrefix\PhpCsFixer\FixerFactory;
use MolliePrefix\Symfony\Component\Console\Command\Command;
use MolliePrefix\Symfony\Component\Console\Input\InputInterface;
use MolliePrefix\Symfony\Component\Console\Output\OutputInterface;
use MolliePrefix\Symfony\Component\Filesystem\Filesystem;
use MolliePrefix\Symfony\Component\Finder\Finder;
use MolliePrefix\Symfony\Component\Finder\SplFileInfo;
/**
 * @internal
 */
final class DocumentationCommand extends \MolliePrefix\Symfony\Component\Console\Command\Command
{
    protected static $defaultName = 'documentation';
    /**
     * @var DocumentationGenerator
     */
    private $generator;
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->generator = new \MolliePrefix\PhpCsFixer\Documentation\DocumentationGenerator();
    }
    protected function configure()
    {
        $this->setAliases(['doc'])->setDescription('Dumps the documentation of the project into its /doc directory.');
    }
    protected function execute(\MolliePrefix\Symfony\Component\Console\Input\InputInterface $input, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $fixerFactory = new \MolliePrefix\PhpCsFixer\FixerFactory();
        $fixerFactory->registerBuiltInFixers();
        /** @var AbstractFixer[] $fixers */
        $fixers = $fixerFactory->getFixers();
        $paths = ['_index' => $this->generator->getFixersDocumentationIndexFilePath()];
        $filesystem = new \MolliePrefix\Symfony\Component\Filesystem\Filesystem();
        foreach ($fixers as $fixer) {
            $class = \get_class($fixer);
            $paths[$class] = $path = $this->generator->getFixerDocumentationFilePath($fixer);
            $filesystem->dumpFile($path, $this->generator->generateFixerDocumentation($fixer));
        }
        /** @var SplFileInfo $file */
        foreach ((new \MolliePrefix\Symfony\Component\Finder\Finder())->files()->in($this->generator->getFixersDocumentationDirectoryPath()) as $file) {
            $path = $file->getPathname();
            if (!\in_array($path, $paths, \true)) {
                $filesystem->remove($path);
            }
        }
        if (\false === @\file_put_contents($paths['_index'], $this->generator->generateFixersDocumentationIndex($fixers))) {
            throw new \RuntimeException("Failed updating file {$paths['_index']}.");
        }
        return 0;
    }
}
