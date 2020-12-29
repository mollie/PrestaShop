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
namespace MolliePrefix\PhpCsFixer\Fixer\PhpTag;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Michele Locati <michele@locati.it>
 */
final class EchoTagSyntaxFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    /** @internal */
    const OPTION_FORMAT = 'format';
    /** @internal */
    const OPTION_SHORTEN_SIMPLE_STATEMENTS_ONLY = 'shorten_simple_statements_only';
    /** @internal */
    const OPTION_LONG_FUNCTION = 'long_function';
    /** @internal */
    const FORMAT_SHORT = 'short';
    /** @internal */
    const FORMAT_LONG = 'long';
    /** @internal */
    const LONG_FUNCTION_ECHO = 'echo';
    /** @internal */
    const LONG_FUNCTION_PRINT = 'print';
    /** @internal */
    const SUPPORTED_FORMAT_OPTIONS = [self::FORMAT_LONG, self::FORMAT_SHORT];
    /** @internal */
    const SUPPORTED_LONGFUNCTION_OPTIONS = [self::LONG_FUNCTION_ECHO, self::LONG_FUNCTION_PRINT];
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        $sample = <<<'EOT'
<?=1?>
<?php print '2' . '3'; ?>
<?php /* comment */ echo '2' . '3'; ?>
<?php print '2' . '3'; someFunction(); ?>

EOT;
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Replaces short-echo `<?=` with long format `<?php echo`/`<?php print` syntax, or vice-versa.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample($sample), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample($sample, [self::OPTION_FORMAT => self::FORMAT_LONG]), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample($sample, [self::OPTION_FORMAT => self::FORMAT_LONG, self::OPTION_LONG_FUNCTION => self::LONG_FUNCTION_PRINT]), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample($sample, [self::OPTION_FORMAT => self::FORMAT_SHORT]), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample($sample, [self::OPTION_FORMAT => self::FORMAT_SHORT, self::OPTION_SHORTEN_SIMPLE_STATEMENTS_ONLY => \false])], null);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before NoMixedEchoPrintFixer.
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
        if (self::FORMAT_SHORT === $this->configuration[self::OPTION_FORMAT]) {
            return $tokens->isAnyTokenKindsFound([\T_ECHO, \T_PRINT]);
        }
        return $tokens->isTokenKindFound(\T_OPEN_TAG_WITH_ECHO);
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder(self::OPTION_FORMAT, 'The desired language construct.'))->setAllowedValues(self::SUPPORTED_FORMAT_OPTIONS)->setDefault(self::FORMAT_LONG)->getOption(), (new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder(self::OPTION_LONG_FUNCTION, 'The function to be used to expand the short echo tags'))->setAllowedValues(self::SUPPORTED_LONGFUNCTION_OPTIONS)->setDefault(self::LONG_FUNCTION_ECHO)->getOption(), (new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder(self::OPTION_SHORTEN_SIMPLE_STATEMENTS_ONLY, 'Render short-echo tags only in case of simple code'))->setAllowedTypes(['bool'])->setDefault(\true)->getOption()]);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        if (self::FORMAT_SHORT === $this->configuration[self::OPTION_FORMAT]) {
            $this->longToShort($tokens);
        } else {
            $this->shortToLong($tokens);
        }
    }
    private function longToShort(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $skipWhenComplexCode = $this->configuration[self::OPTION_SHORTEN_SIMPLE_STATEMENTS_ONLY];
        $count = $tokens->count();
        for ($index = 0; $index < $count; ++$index) {
            if (!$tokens[$index]->isGivenKind(\T_OPEN_TAG)) {
                continue;
            }
            $nextMeaningful = $tokens->getNextMeaningfulToken($index);
            if (null === $nextMeaningful) {
                return;
            }
            if (!$tokens[$nextMeaningful]->isGivenKind([\T_ECHO, \T_PRINT])) {
                $index = $nextMeaningful;
                continue;
            }
            if ($skipWhenComplexCode && $this->isComplexCode($tokens, $nextMeaningful + 1)) {
                $index = $nextMeaningful;
                continue;
            }
            $newTokens = $this->buildLongToShortTokens($tokens, $index, $nextMeaningful);
            $tokens->overrideRange($index, $nextMeaningful, $newTokens);
            $count = $tokens->count();
        }
    }
    private function shortToLong(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        if (self::LONG_FUNCTION_PRINT === $this->configuration[self::OPTION_LONG_FUNCTION]) {
            $echoToken = [\T_PRINT, 'print'];
        } else {
            $echoToken = [\T_ECHO, 'echo'];
        }
        $index = -1;
        while (\true) {
            $index = $tokens->getNextTokenOfKind($index, [[\T_OPEN_TAG_WITH_ECHO]]);
            if (null === $index) {
                return;
            }
            $replace = [new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_OPEN_TAG, '<?php ']), new \MolliePrefix\PhpCsFixer\Tokenizer\Token($echoToken)];
            if (!$tokens[$index + 1]->isWhitespace()) {
                $replace[] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']);
            }
            $tokens->overrideRange($index, $index, $replace);
            ++$index;
        }
    }
    /**
     * Check if $tokens, starting at $index, contains "complex code", that is, the content
     * of the echo tag contains more than a simple "echo something".
     *
     * This is done by a very quick test: if the tag contains non-whitespace tokens after
     * a semicolon, we consider it as "complex".
     *
     * @param int $index
     *
     * @return bool
     *
     * @example `<?php echo 1 ?>` is false (not complex)
     * @example `<?php echo 'hello' . 'world'; ?>` is false (not "complex")
     * @example `<?php echo 2; $set = 3 ?>` is true ("complex")
     */
    private function isComplexCode(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        $semicolonFound = \false;
        for ($count = $tokens->count(); $index < $count; ++$index) {
            $token = $tokens[$index];
            if ($token->isGivenKind(\T_CLOSE_TAG)) {
                return \false;
            }
            if (';' === $token->getContent()) {
                $semicolonFound = \true;
            } elseif ($semicolonFound && !$token->isWhitespace()) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * Builds the list of tokens that replace a long echo sequence.
     *
     * @param int $openTagIndex
     * @param int $echoTagIndex
     *
     * @return Token[]
     */
    private function buildLongToShortTokens(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $openTagIndex, $echoTagIndex)
    {
        $result = [new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_OPEN_TAG_WITH_ECHO, '<?='])];
        $start = $tokens->getNextNonWhitespace($openTagIndex);
        if ($start === $echoTagIndex) {
            // No non-whitespace tokens between $openTagIndex and $echoTagIndex
            return $result;
        }
        // Find the last non-whitespace index before $echoTagIndex
        $end = $echoTagIndex - 1;
        while ($tokens[$end]->isWhitespace()) {
            --$end;
        }
        // Copy the non-whitespace tokens between $openTagIndex and $echoTagIndex
        for ($index = $start; $index <= $end; ++$index) {
            $result[] = clone $tokens[$index];
        }
        return $result;
    }
}
