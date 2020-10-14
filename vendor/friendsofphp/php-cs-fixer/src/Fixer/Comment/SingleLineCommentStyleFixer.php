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
namespace MolliePrefix\PhpCsFixer\Fixer\Comment;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\AllowedValueSubset;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Preg;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Filippo Tessarotto <zoeslam@gmail.com>
 */
final class SingleLineCommentStyleFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    /**
     * @var bool
     */
    private $asteriskEnabled;
    /**
     * @var bool
     */
    private $hashEnabled;
    /**
     * {@inheritdoc}
     */
    public function configure(array $configuration = null)
    {
        parent::configure($configuration);
        $this->asteriskEnabled = \in_array('asterisk', $this->configuration['comment_types'], \true);
        $this->hashEnabled = \in_array('hash', $this->configuration['comment_types'], \true);
    }
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Single-line comments and multi-line comments with only one line of actual content should use the `//` syntax.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
/* asterisk comment */
$a = 1;

# hash comment
$b = 2;

/*
 * multi-line
 * comment
 */
$c = 3;
'), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
/* first comment */
$a = 1;

/*
 * second comment
 */
$b = 2;

/*
 * third
 * comment
 */
$c = 3;
', ['comment_types' => ['asterisk']]), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php # comment\n", ['comment_types' => ['hash']])]);
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isTokenKindFound(\T_COMMENT);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind(\T_COMMENT)) {
                continue;
            }
            $content = $token->getContent();
            $commentContent = \substr($content, 2, -2) ?: '';
            if ($this->hashEnabled && '#' === $content[0]) {
                $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([$token->getId(), '//' . \substr($content, 1)]);
                continue;
            }
            if (!$this->asteriskEnabled || \false !== \strpos($commentContent, '?>') || '/*' !== \substr($content, 0, 2) || 1 === \MolliePrefix\PhpCsFixer\Preg::match('/[^\\s\\*].*\\R.*[^\\s\\*]/s', $commentContent)) {
                continue;
            }
            $nextTokenIndex = $index + 1;
            if (isset($tokens[$nextTokenIndex])) {
                $nextToken = $tokens[$nextTokenIndex];
                if (!$nextToken->isWhitespace() || 1 !== \MolliePrefix\PhpCsFixer\Preg::match('/\\R/', $nextToken->getContent())) {
                    continue;
                }
                $tokens[$nextTokenIndex] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([$nextToken->getId(), \ltrim($nextToken->getContent(), " \t")]);
            }
            $content = '//';
            if (1 === \MolliePrefix\PhpCsFixer\Preg::match('/[^\\s\\*]/', $commentContent)) {
                $content = '// ' . \MolliePrefix\PhpCsFixer\Preg::replace('/[\\s\\*]*([^\\s\\*](?:.+[^\\s\\*])?)[\\s\\*]*/', '\\1', $commentContent);
            }
            $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([$token->getId(), $content]);
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('comment_types', 'List of comment types to fix'))->setAllowedTypes(['array'])->setAllowedValues([new \MolliePrefix\PhpCsFixer\FixerConfiguration\AllowedValueSubset(['asterisk', 'hash'])])->setDefault(['asterisk', 'hash'])->getOption()]);
    }
}
