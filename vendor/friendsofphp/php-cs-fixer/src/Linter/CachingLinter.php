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
namespace MolliePrefix\PhpCsFixer\Linter;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
final class CachingLinter implements \MolliePrefix\PhpCsFixer\Linter\LinterInterface
{
    /**
     * @var LinterInterface
     */
    private $sublinter;
    /**
     * @var array<int, LintingResultInterface>
     */
    private $cache = [];
    public function __construct(\MolliePrefix\PhpCsFixer\Linter\LinterInterface $linter)
    {
        $this->sublinter = $linter;
    }
    /**
     * {@inheritdoc}
     */
    public function isAsync()
    {
        return $this->sublinter->isAsync();
    }
    /**
     * {@inheritdoc}
     */
    public function lintFile($path)
    {
        $checksum = \crc32(\file_get_contents($path));
        if (!isset($this->cache[$checksum])) {
            $this->cache[$checksum] = $this->sublinter->lintFile($path);
        }
        return $this->cache[$checksum];
    }
    /**
     * {@inheritdoc}
     */
    public function lintSource($source)
    {
        $checksum = \crc32($source);
        if (!isset($this->cache[$checksum])) {
            $this->cache[$checksum] = $this->sublinter->lintSource($source);
        }
        return $this->cache[$checksum];
    }
}
