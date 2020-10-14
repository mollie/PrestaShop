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
namespace MolliePrefix\PhpCsFixer\Fixer\ArrayNotation;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerConfiguration\InvalidOptionsForEnvException;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification;
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use MolliePrefix\PhpCsFixer\Tokenizer\CT;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
use MolliePrefix\Symfony\Component\OptionsResolver\Options;
/**
 * @author Adam Marczuk <adam@marczuk.info>
 */
final class NoWhitespaceBeforeCommaInArrayFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('In array declaration, there MUST NOT be a whitespace before each comma.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php \$x = array(1 , \"2\");\n"), new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample(<<<'SAMPLE'
<?php

namespace MolliePrefix;

$x = [<<<EOD
foo
EOD
, 'bar'];

SAMPLE
, new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification(70300), ['after_heredoc' => \true])]);
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAnyTokenKindsFound([\T_ARRAY, \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_ARRAY_SQUARE_BRACE_OPEN]);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        for ($index = $tokens->count() - 1; $index >= 0; --$index) {
            if ($tokens[$index]->isGivenKind([\T_ARRAY, \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_ARRAY_SQUARE_BRACE_OPEN])) {
                $this->fixSpacing($index, $tokens);
            }
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('after_heredoc', 'Whether the whitespace between heredoc end and comma should be removed.'))->setAllowedTypes(['bool'])->setDefault(\false)->setNormalizer(static function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options, $value) {
            if (\PHP_VERSION_ID < 70300 && $value) {
                throw new \MolliePrefix\PhpCsFixer\FixerConfiguration\InvalidOptionsForEnvException('"after_heredoc" option can only be enabled with PHP 7.3+.');
            }
            return $value;
        })->getOption()]);
    }
    /**
     * Method to fix spacing in array declaration.
     *
     * @param int $index
     */
    private function fixSpacing($index, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        if ($tokens[$index]->isGivenKind(\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_ARRAY_SQUARE_BRACE_OPEN)) {
            $startIndex = $index;
            $endIndex = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_ARRAY_SQUARE_BRACE, $startIndex);
        } else {
            $startIndex = $tokens->getNextTokenOfKind($index, ['(']);
            $endIndex = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $startIndex);
        }
        for ($i = $endIndex - 1; $i > $startIndex; --$i) {
            $i = $this->skipNonArrayElements($i, $tokens);
            $currentToken = $tokens[$i];
            $prevIndex = $tokens->getPrevNonWhitespace($i - 1);
            if ($currentToken->equals(',') && !$tokens[$prevIndex]->isComment() && ($this->configuration['after_heredoc'] || !$tokens[$prevIndex]->equals([\T_END_HEREDOC]))) {
                $tokens->removeLeadingWhitespace($i);
            }
        }
    }
    /**
     * Method to move index over the non-array elements like function calls or function declarations.
     *
     * @param int $index
     *
     * @return int New index
     */
    private function skipNonArrayElements($index, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        if ($tokens[$index]->equals('}')) {
            return $tokens->findBlockStart(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_CURLY_BRACE, $index);
        }
        if ($tokens[$index]->equals(')')) {
            $startIndex = $tokens->findBlockStart(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $index);
            $startIndex = $tokens->getPrevMeaningfulToken($startIndex);
            if (!$tokens[$startIndex]->isGivenKind([\T_ARRAY, \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_ARRAY_SQUARE_BRACE_OPEN])) {
                return $startIndex;
            }
        }
        return $index;
    }
}
