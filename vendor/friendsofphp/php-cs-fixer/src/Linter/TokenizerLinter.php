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

use MolliePrefix\PhpCsFixer\FileReader;
use MolliePrefix\PhpCsFixer\Tokenizer\CodeHasher;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * Handle PHP code linting.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
final class TokenizerLinter implements \MolliePrefix\PhpCsFixer\Linter\LinterInterface
{
    public function __construct()
    {
        if (\false === \defined('TOKEN_PARSE') || \false === \class_exists(\MolliePrefix\CompileError::class)) {
            throw new \MolliePrefix\PhpCsFixer\Linter\UnavailableLinterException('Cannot use tokenizer as linter.');
        }
    }
    /**
     * {@inheritdoc}
     */
    public function isAsync()
    {
        return \false;
    }
    /**
     * {@inheritdoc}
     */
    public function lintFile($path)
    {
        return $this->lintSource(\MolliePrefix\PhpCsFixer\FileReader::createSingleton()->read($path));
    }
    /**
     * {@inheritdoc}
     */
    public function lintSource($source)
    {
        try {
            // To lint, we will parse the source into Tokens.
            // During that process, it might throw a ParseError or CompileError.
            // If it won't, cache of tokenized version of source will be kept, which is great for Runner.
            // Yet, first we need to clear already existing cache to not hit it and lint the code indeed.
            $codeHash = \MolliePrefix\PhpCsFixer\Tokenizer\CodeHasher::calculateCodeHash($source);
            \MolliePrefix\PhpCsFixer\Tokenizer\Tokens::clearCache($codeHash);
            \MolliePrefix\PhpCsFixer\Tokenizer\Tokens::fromCode($source);
            return new \MolliePrefix\PhpCsFixer\Linter\TokenizerLintingResult();
        } catch (\ParseError $e) {
            return new \MolliePrefix\PhpCsFixer\Linter\TokenizerLintingResult($e);
        } catch (\MolliePrefix\CompileError $e) {
            return new \MolliePrefix\PhpCsFixer\Linter\TokenizerLintingResult($e);
        }
    }
}
