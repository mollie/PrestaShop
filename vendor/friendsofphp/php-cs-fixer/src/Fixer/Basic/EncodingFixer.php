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
namespace MolliePrefix\PhpCsFixer\Fixer\Basic;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * Fixer for rules defined in PSR1 ¶2.2.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class EncodingFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    private $BOM;
    public function __construct()
    {
        parent::__construct();
        $this->BOM = \pack('CCC', 0xef, 0xbb, 0xbf);
    }
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('PHP code MUST use only UTF-8 without BOM (remove BOM).', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample($this->BOM . '<?php

echo "Hello!";
')]);
    }
    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        // must run first (at least before Fixers that using Tokens) - for speed reason of whole fixing process
        return 100;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return \true;
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $content = $tokens[0]->getContent();
        if (0 === \strncmp($content, $this->BOM, 3)) {
            /** @var false|string $newContent until support for PHP 5.6 is dropped */
            $newContent = \substr($content, 3);
            if (\false === $newContent) {
                $newContent = '';
                // substr returns false rather than an empty string when starting at the end
            }
            if ('' === $newContent) {
                $tokens->clearAt(0);
            } else {
                $tokens[0] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([$tokens[0]->getId(), $newContent]);
            }
        }
    }
}
