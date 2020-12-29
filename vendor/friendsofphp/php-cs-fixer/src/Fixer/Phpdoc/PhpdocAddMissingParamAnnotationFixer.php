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
use MolliePrefix\PhpCsFixer\DocBlock\DocBlock;
use MolliePrefix\PhpCsFixer\DocBlock\Line;
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Preg;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class PhpdocAddMissingParamAnnotationFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface, \MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('PHPDoc should contain `@param` for all params.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
/**
 * @param int $bar
 *
 * @return void
 */
function f9(string $foo, $bar, $baz) {}
'), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
/**
 * @param int $bar
 *
 * @return void
 */
function f9(string $foo, $bar, $baz) {}
', ['only_untyped' => \true]), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
/**
 * @param int $bar
 *
 * @return void
 */
function f9(string $foo, $bar, $baz) {}
', ['only_untyped' => \false])]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before NoEmptyPhpdocFixer, NoSuperfluousPhpdocTagsFixer, PhpdocAlignFixer, PhpdocAlignFixer, PhpdocOrderFixer.
     * Must run after CommentToPhpdocFixer, GeneralPhpdocTagRenameFixer, PhpdocIndentFixer, PhpdocNoAliasTagFixer, PhpdocScalarFixer, PhpdocToCommentFixer, PhpdocTypesFixer.
     */
    public function getPriority()
    {
        return 10;
    }
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
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $argumentsAnalyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer();
        for ($index = 0, $limit = $tokens->count(); $index < $limit; ++$index) {
            $token = $tokens[$index];
            if (!$token->isGivenKind(\T_DOC_COMMENT)) {
                continue;
            }
            $tokenContent = $token->getContent();
            if (\false !== \stripos($tokenContent, 'inheritdoc')) {
                continue;
            }
            // ignore one-line phpdocs like `/** foo */`, as there is no place to put new annotations
            if (\false === \strpos($tokenContent, "\n")) {
                continue;
            }
            $mainIndex = $index;
            $index = $tokens->getNextMeaningfulToken($index);
            if (null === $index) {
                return;
            }
            while ($tokens[$index]->isGivenKind([\T_ABSTRACT, \T_FINAL, \T_PRIVATE, \T_PROTECTED, \T_PUBLIC, \T_STATIC, \T_VAR])) {
                $index = $tokens->getNextMeaningfulToken($index);
            }
            if (!$tokens[$index]->isGivenKind(\T_FUNCTION)) {
                continue;
            }
            $openIndex = $tokens->getNextTokenOfKind($index, ['(']);
            $index = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openIndex);
            $arguments = [];
            foreach ($argumentsAnalyzer->getArguments($tokens, $openIndex, $index) as $start => $end) {
                $argumentInfo = $this->prepareArgumentInformation($tokens, $start, $end);
                if (!$this->configuration['only_untyped'] || '' === $argumentInfo['type']) {
                    $arguments[$argumentInfo['name']] = $argumentInfo;
                }
            }
            if (!\count($arguments)) {
                continue;
            }
            $doc = new \MolliePrefix\PhpCsFixer\DocBlock\DocBlock($tokenContent);
            $lastParamLine = null;
            foreach ($doc->getAnnotationsOfType('param') as $annotation) {
                $pregMatched = \MolliePrefix\PhpCsFixer\Preg::match('/^[^$]+(\\$\\w+).*$/s', $annotation->getContent(), $matches);
                if (1 === $pregMatched) {
                    unset($arguments[$matches[1]]);
                }
                $lastParamLine = \max($lastParamLine, $annotation->getEnd());
            }
            if (!\count($arguments)) {
                continue;
            }
            $lines = $doc->getLines();
            $linesCount = \count($lines);
            \MolliePrefix\PhpCsFixer\Preg::match('/^(\\s*).*$/', $lines[$linesCount - 1]->getContent(), $matches);
            $indent = $matches[1];
            $newLines = [];
            foreach ($arguments as $argument) {
                $type = $argument['type'] ?: 'mixed';
                if ('?' !== $type[0] && 'null' === \strtolower($argument['default'])) {
                    $type = 'null|' . $type;
                }
                $newLines[] = new \MolliePrefix\PhpCsFixer\DocBlock\Line(\sprintf('%s* @param %s %s%s', $indent, $type, $argument['name'], $this->whitespacesConfig->getLineEnding()));
            }
            \array_splice($lines, $lastParamLine ? $lastParamLine + 1 : $linesCount - 1, 0, $newLines);
            $tokens[$mainIndex] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_DOC_COMMENT, \implode('', $lines)]);
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('only_untyped', 'Whether to add missing `@param` annotations for untyped parameters only.'))->setDefault(\true)->setAllowedTypes(['bool'])->getOption()]);
    }
    /**
     * @param int $start
     * @param int $end
     *
     * @return array
     */
    private function prepareArgumentInformation(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $start, $end)
    {
        $info = ['default' => '', 'name' => '', 'type' => ''];
        $sawName = \false;
        for ($index = $start; $index <= $end; ++$index) {
            $token = $tokens[$index];
            if ($token->isComment() || $token->isWhitespace()) {
                continue;
            }
            if ($token->isGivenKind(\T_VARIABLE)) {
                $sawName = \true;
                $info['name'] = $token->getContent();
                continue;
            }
            if ($token->equals('=')) {
                continue;
            }
            if ($sawName) {
                $info['default'] .= $token->getContent();
            } elseif ('&' !== $token->getContent()) {
                if ($token->isGivenKind(\T_ELLIPSIS)) {
                    if ('' === $info['type']) {
                        $info['type'] = 'array';
                    } else {
                        $info['type'] .= '[]';
                    }
                } else {
                    $info['type'] .= $token->getContent();
                }
            }
        }
        return $info;
    }
}
