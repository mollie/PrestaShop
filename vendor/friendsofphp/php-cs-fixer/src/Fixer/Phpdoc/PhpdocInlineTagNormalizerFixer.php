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
namespace MolliePrefix\PhpCsFixer\Fixer\Phpdoc;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Preg;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author SpacePossum
 */
final class PhpdocInlineTagNormalizerFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isTokenKindFound(\T_DOC_COMMENT);
    }
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Fixes PHPDoc inline tags.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n/**\n * @{TUTORIAL}\n * {{ @link }}\n * @inheritDoc\n */\n"), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n/**\n * @{TUTORIAL}\n * {{ @link }}\n * @inheritDoc\n */\n", ['tags' => ['TUTORIAL']])]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before PhpdocAlignFixer.
     * Must run after CommentToPhpdocFixer, PhpdocIndentFixer, PhpdocScalarFixer, PhpdocToCommentFixer, PhpdocTypesFixer.
     */
    public function getPriority()
    {
        return 0;
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        if (!$this->configuration['tags']) {
            return;
        }
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind(\T_DOC_COMMENT)) {
                continue;
            }
            // Move `@` inside tag, for example @{tag} -> {@tag}, replace multiple curly brackets,
            // remove spaces between '{' and '@', remove white space between end
            // of text and closing bracket and between the tag and inline comment.
            $content = \MolliePrefix\PhpCsFixer\Preg::replaceCallback(\sprintf('#(?:@{+|{+\\h*@)\\h*(%s)s?([^}]*)(?:}+)#i', \implode('|', \array_map(function ($tag) {
                return \preg_quote($tag, '/');
            }, $this->configuration['tags']))), function (array $matches) {
                $doc = \trim($matches[2]);
                if ('' === $doc) {
                    return '{@' . $matches[1] . '}';
                }
                return '{@' . $matches[1] . ' ' . $doc . '}';
            }, $token->getContent());
            $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_DOC_COMMENT, $content]);
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('tags', 'The list of tags to normalize'))->setAllowedTypes(['array'])->setDefault(['example', 'id', 'internal', 'inheritdoc', 'inheritdocs', 'link', 'source', 'toc', 'tutorial'])->getOption()]);
    }
}
