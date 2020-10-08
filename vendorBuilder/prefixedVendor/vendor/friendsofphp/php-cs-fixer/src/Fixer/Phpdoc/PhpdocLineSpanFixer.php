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
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\CT;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
use MolliePrefix\PhpCsFixer\Tokenizer\TokensAnalyzer;
/**
 * @author Gert de Pagter <BackEndTea@gmail.com>
 */
final class PhpdocLineSpanFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface, \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Changes doc blocks from single to multi line, or reversed. Works for class constants, properties and methods only.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n\nclass Foo{\n    /** @var bool */\n    public \$var;\n}\n"), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n\nclass Foo{\n    /**\n    * @var bool\n    */\n    public \$var;\n}\n", ['property' => 'single'])]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before PhpdocAlignFixer.
     * Must run after CommentToPhpdocFixer, GeneralPhpdocAnnotationRemoveFixer, PhpdocIndentFixer, PhpdocScalarFixer, PhpdocToCommentFixer, PhpdocTypesFixer.
     */
    public function getPriority()
    {
        return 0;
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
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('const', 'Whether const blocks should be single or multi line'))->setAllowedValues(['single', 'multi'])->setDefault('multi')->getOption(), (new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('property', 'Whether property doc blocks should be single or multi line'))->setAllowedValues(['single', 'multi'])->setDefault('multi')->getOption(), (new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('method', 'Whether method doc blocks should be single or multi line'))->setAllowedValues(['single', 'multi'])->setDefault('multi')->getOption()]);
    }
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $analyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\TokensAnalyzer($tokens);
        $elements = $analyzer->getClassyElements();
        foreach ($elements as $index => $element) {
            if (!$this->hasDocBlock($tokens, $index)) {
                continue;
            }
            $type = $element['type'];
            $docIndex = $this->getDocBlockIndex($tokens, $index);
            $doc = new \MolliePrefix\PhpCsFixer\DocBlock\DocBlock($tokens[$docIndex]->getContent());
            if ('multi' === $this->configuration[$type]) {
                $doc->makeMultiLine($originalIndent = $this->detectIndent($tokens, $docIndex), $this->whitespacesConfig->getLineEnding());
            } else {
                $doc->makeSingleLine();
            }
            $tokens->offsetSet($docIndex, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_DOC_COMMENT, $doc->getContent()]));
        }
    }
    /**
     * @param int $index
     *
     * @return bool
     */
    private function hasDocBlock(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        $docBlockIndex = $this->getDocBlockIndex($tokens, $index);
        return $tokens[$docBlockIndex]->isGivenKind(\T_DOC_COMMENT);
    }
    /**
     * @param int $index
     *
     * @return int
     */
    private function getDocBlockIndex(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        do {
            $index = $tokens->getPrevNonWhitespace($index);
        } while ($tokens[$index]->isGivenKind([\T_PUBLIC, \T_PROTECTED, \T_PRIVATE, \T_FINAL, \T_ABSTRACT, \T_COMMENT, \T_VAR, \T_STATIC, \T_STRING, \T_NS_SEPARATOR, \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_NULLABLE_TYPE]));
        return $index;
    }
    /**
     * @param int $index
     *
     * @return string
     */
    private function detectIndent(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        if (!$tokens[$index - 1]->isWhitespace()) {
            return '';
            // cannot detect indent
        }
        $explodedContent = \explode($this->whitespacesConfig->getLineEnding(), $tokens[$index - 1]->getContent());
        return \end($explodedContent);
    }
}
