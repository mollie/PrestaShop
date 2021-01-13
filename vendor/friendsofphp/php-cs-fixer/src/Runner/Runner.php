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
namespace MolliePrefix\PhpCsFixer\Runner;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\Cache\CacheManagerInterface;
use MolliePrefix\PhpCsFixer\Cache\Directory;
use MolliePrefix\PhpCsFixer\Cache\DirectoryInterface;
use MolliePrefix\PhpCsFixer\Differ\DifferInterface;
use MolliePrefix\PhpCsFixer\Error\Error;
use MolliePrefix\PhpCsFixer\Error\ErrorsManager;
use MolliePrefix\PhpCsFixer\Event\Event;
use MolliePrefix\PhpCsFixer\FileReader;
use MolliePrefix\PhpCsFixer\Fixer\FixerInterface;
use MolliePrefix\PhpCsFixer\FixerFileProcessedEvent;
use MolliePrefix\PhpCsFixer\Linter\LinterInterface;
use MolliePrefix\PhpCsFixer\Linter\LintingException;
use MolliePrefix\PhpCsFixer\Linter\LintingResultInterface;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
use MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcherInterface;
use MolliePrefix\Symfony\Component\Filesystem\Exception\IOException;
/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class Runner
{
    /**
     * @var DifferInterface
     */
    private $differ;
    /**
     * @var DirectoryInterface
     */
    private $directory;
    /**
     * @var null|EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var ErrorsManager
     */
    private $errorsManager;
    /**
     * @var CacheManagerInterface
     */
    private $cacheManager;
    /**
     * @var bool
     */
    private $isDryRun;
    /**
     * @var LinterInterface
     */
    private $linter;
    /**
     * @var \Traversable
     */
    private $finder;
    /**
     * @var FixerInterface[]
     */
    private $fixers;
    /**
     * @var bool
     */
    private $stopOnViolation;
    public function __construct($finder, array $fixers, \MolliePrefix\PhpCsFixer\Differ\DifferInterface $differ, \MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher = null, \MolliePrefix\PhpCsFixer\Error\ErrorsManager $errorsManager, \MolliePrefix\PhpCsFixer\Linter\LinterInterface $linter, $isDryRun, \MolliePrefix\PhpCsFixer\Cache\CacheManagerInterface $cacheManager, \MolliePrefix\PhpCsFixer\Cache\DirectoryInterface $directory = null, $stopOnViolation = \false)
    {
        $this->finder = $finder;
        $this->fixers = $fixers;
        $this->differ = $differ;
        $this->eventDispatcher = $eventDispatcher;
        $this->errorsManager = $errorsManager;
        $this->linter = $linter;
        $this->isDryRun = $isDryRun;
        $this->cacheManager = $cacheManager;
        $this->directory = $directory ?: new \MolliePrefix\PhpCsFixer\Cache\Directory('');
        $this->stopOnViolation = $stopOnViolation;
    }
    /**
     * @return array
     */
    public function fix()
    {
        $changed = [];
        $finder = $this->finder;
        $finderIterator = $finder instanceof \IteratorAggregate ? $finder->getIterator() : $finder;
        $fileFilteredFileIterator = new \MolliePrefix\PhpCsFixer\Runner\FileFilterIterator($finderIterator, $this->eventDispatcher, $this->cacheManager);
        $collection = $this->linter->isAsync() ? new \MolliePrefix\PhpCsFixer\Runner\FileCachingLintingIterator($fileFilteredFileIterator, $this->linter) : new \MolliePrefix\PhpCsFixer\Runner\FileLintingIterator($fileFilteredFileIterator, $this->linter);
        foreach ($collection as $file) {
            $fixInfo = $this->fixFile($file, $collection->currentLintingResult());
            // we do not need Tokens to still caching just fixed file - so clear the cache
            \MolliePrefix\PhpCsFixer\Tokenizer\Tokens::clearCache();
            if ($fixInfo) {
                $name = $this->directory->getRelativePathTo($file);
                $changed[$name] = $fixInfo;
                if ($this->stopOnViolation) {
                    break;
                }
            }
        }
        return $changed;
    }
    private function fixFile(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Linter\LintingResultInterface $lintingResult)
    {
        $name = $file->getPathname();
        try {
            $lintingResult->check();
        } catch (\MolliePrefix\PhpCsFixer\Linter\LintingException $e) {
            $this->dispatchEvent(\MolliePrefix\PhpCsFixer\FixerFileProcessedEvent::NAME, new \MolliePrefix\PhpCsFixer\FixerFileProcessedEvent(\MolliePrefix\PhpCsFixer\FixerFileProcessedEvent::STATUS_INVALID));
            $this->errorsManager->report(new \MolliePrefix\PhpCsFixer\Error\Error(\MolliePrefix\PhpCsFixer\Error\Error::TYPE_INVALID, $name, $e));
            return;
        }
        $old = \MolliePrefix\PhpCsFixer\FileReader::createSingleton()->read($file->getRealPath());
        \MolliePrefix\PhpCsFixer\Tokenizer\Tokens::setLegacyMode(\false);
        $tokens = \MolliePrefix\PhpCsFixer\Tokenizer\Tokens::fromCode($old);
        $oldHash = $tokens->getCodeHash();
        $newHash = $oldHash;
        $new = $old;
        $appliedFixers = [];
        try {
            foreach ($this->fixers as $fixer) {
                // for custom fixers we don't know is it safe to run `->fix()` without checking `->supports()` and `->isCandidate()`,
                // thus we need to check it and conditionally skip fixing
                if (!$fixer instanceof \MolliePrefix\PhpCsFixer\AbstractFixer && (!$fixer->supports($file) || !$fixer->isCandidate($tokens))) {
                    continue;
                }
                $fixer->fix($file, $tokens);
                if ($tokens->isChanged()) {
                    $tokens->clearEmptyTokens();
                    $tokens->clearChanged();
                    $appliedFixers[] = $fixer->getName();
                }
            }
        } catch (\Exception $e) {
            $this->processException($name, $e);
            return;
        } catch (\ParseError $e) {
            $this->dispatchEvent(\MolliePrefix\PhpCsFixer\FixerFileProcessedEvent::NAME, new \MolliePrefix\PhpCsFixer\FixerFileProcessedEvent(\MolliePrefix\PhpCsFixer\FixerFileProcessedEvent::STATUS_LINT));
            $this->errorsManager->report(new \MolliePrefix\PhpCsFixer\Error\Error(\MolliePrefix\PhpCsFixer\Error\Error::TYPE_LINT, $name, $e));
            return;
        } catch (\Throwable $e) {
            $this->processException($name, $e);
            return;
        }
        $fixInfo = null;
        if (!empty($appliedFixers)) {
            $new = $tokens->generateCode();
            $newHash = $tokens->getCodeHash();
        }
        // We need to check if content was changed and then applied changes.
        // But we can't simple check $appliedFixers, because one fixer may revert
        // work of other and both of them will mark collection as changed.
        // Therefore we need to check if code hashes changed.
        if ($oldHash !== $newHash) {
            $fixInfo = ['appliedFixers' => $appliedFixers, 'diff' => $this->differ->diff($old, $new)];
            try {
                $this->linter->lintSource($new)->check();
            } catch (\MolliePrefix\PhpCsFixer\Linter\LintingException $e) {
                $this->dispatchEvent(\MolliePrefix\PhpCsFixer\FixerFileProcessedEvent::NAME, new \MolliePrefix\PhpCsFixer\FixerFileProcessedEvent(\MolliePrefix\PhpCsFixer\FixerFileProcessedEvent::STATUS_LINT));
                $this->errorsManager->report(new \MolliePrefix\PhpCsFixer\Error\Error(\MolliePrefix\PhpCsFixer\Error\Error::TYPE_LINT, $name, $e, $fixInfo['appliedFixers'], $fixInfo['diff']));
                return;
            }
            if (!$this->isDryRun) {
                $fileName = $file->getRealPath();
                if (!\file_exists($fileName)) {
                    throw new \MolliePrefix\Symfony\Component\Filesystem\Exception\IOException(\sprintf('Failed to write file "%s" (no longer) exists.', $file->getPathname()), 0, null, $file->getPathname());
                }
                if (\is_dir($fileName)) {
                    throw new \MolliePrefix\Symfony\Component\Filesystem\Exception\IOException(\sprintf('Cannot write file "%s" as the location exists as directory.', $fileName), 0, null, $fileName);
                }
                if (!\is_writable($fileName)) {
                    throw new \MolliePrefix\Symfony\Component\Filesystem\Exception\IOException(\sprintf('Cannot write to file "%s" as it is not writable.', $fileName), 0, null, $fileName);
                }
                if (\false === @\file_put_contents($fileName, $new)) {
                    $error = \error_get_last();
                    throw new \MolliePrefix\Symfony\Component\Filesystem\Exception\IOException(\sprintf('Failed to write file "%s", "%s".', $fileName, $error ? $error['message'] : 'no reason available'), 0, null, $file);
                }
            }
        }
        $this->cacheManager->setFile($name, $new);
        $this->dispatchEvent(\MolliePrefix\PhpCsFixer\FixerFileProcessedEvent::NAME, new \MolliePrefix\PhpCsFixer\FixerFileProcessedEvent($fixInfo ? \MolliePrefix\PhpCsFixer\FixerFileProcessedEvent::STATUS_FIXED : \MolliePrefix\PhpCsFixer\FixerFileProcessedEvent::STATUS_NO_CHANGES));
        return $fixInfo;
    }
    /**
     * Process an exception that occurred.
     *
     * @param string     $name
     * @param \Throwable $e
     */
    private function processException($name, $e)
    {
        $this->dispatchEvent(\MolliePrefix\PhpCsFixer\FixerFileProcessedEvent::NAME, new \MolliePrefix\PhpCsFixer\FixerFileProcessedEvent(\MolliePrefix\PhpCsFixer\FixerFileProcessedEvent::STATUS_EXCEPTION));
        $this->errorsManager->report(new \MolliePrefix\PhpCsFixer\Error\Error(\MolliePrefix\PhpCsFixer\Error\Error::TYPE_EXCEPTION, $name, $e));
    }
    /**
     * @param string $name
     */
    private function dispatchEvent($name, \MolliePrefix\PhpCsFixer\Event\Event $event)
    {
        if (null === $this->eventDispatcher) {
            return;
        }
        // BC compatibility < Sf 4.3
        if (!$this->eventDispatcher instanceof \MolliePrefix\Symfony\Contracts\EventDispatcher\EventDispatcherInterface) {
            $this->eventDispatcher->dispatch($name, $event);
            return;
        }
        $this->eventDispatcher->dispatch($event, $name);
    }
}
