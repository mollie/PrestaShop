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

use MolliePrefix\PhpCsFixer\Cache\CacheManagerInterface;
use MolliePrefix\PhpCsFixer\Event\Event;
use MolliePrefix\PhpCsFixer\FileReader;
use MolliePrefix\PhpCsFixer\FixerFileProcessedEvent;
use MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcherInterface;
/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
final class FileFilterIterator extends \FilterIterator
{
    /**
     * @var null|EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var CacheManagerInterface
     */
    private $cacheManager;
    /**
     * @var array<string,bool>
     */
    private $visitedElements = [];
    public function __construct(\Traversable $iterator, \MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher = null, \MolliePrefix\PhpCsFixer\Cache\CacheManagerInterface $cacheManager)
    {
        if (!$iterator instanceof \Iterator) {
            $iterator = new \IteratorIterator($iterator);
        }
        parent::__construct($iterator);
        $this->eventDispatcher = $eventDispatcher;
        $this->cacheManager = $cacheManager;
    }
    public function accept()
    {
        $file = $this->current();
        if (!$file instanceof \SplFileInfo) {
            throw new \RuntimeException(\sprintf('Expected instance of "\\SplFileInfo", got "%s".', \is_object($file) ? \get_class($file) : \gettype($file)));
        }
        $path = $file->isLink() ? $file->getPathname() : $file->getRealPath();
        if (isset($this->visitedElements[$path])) {
            return \false;
        }
        $this->visitedElements[$path] = \true;
        if (!$file->isFile() || $file->isLink()) {
            return \false;
        }
        $content = \MolliePrefix\PhpCsFixer\FileReader::createSingleton()->read($path);
        // mark as skipped:
        if ('' === $content || !$this->cacheManager->needFixing($file->getPathname(), $content)) {
            $this->dispatchEvent(\MolliePrefix\PhpCsFixer\FixerFileProcessedEvent::NAME, new \MolliePrefix\PhpCsFixer\FixerFileProcessedEvent(\MolliePrefix\PhpCsFixer\FixerFileProcessedEvent::STATUS_SKIPPED));
            return \false;
        }
        return \true;
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
