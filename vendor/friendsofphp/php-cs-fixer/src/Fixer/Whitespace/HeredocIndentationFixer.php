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
namespace MolliePrefix\PhpCsFixer\Fixer\Whitespace;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification;
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use MolliePrefix\PhpCsFixer\Preg;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Gregor Harlan
 */
final class HeredocIndentationFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface, \MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Heredoc/nowdoc content must be properly indented. Requires PHP >= 7.3.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample(<<<'SAMPLE'
<?php

namespace MolliePrefix;

$a = <<<EOD
abc
    def
EOD
;

SAMPLE
, new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification(70300)), new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample(<<<'SAMPLE'
<?php

namespace MolliePrefix;

$a = <<<'EOD'
abc
    def
EOD
;

SAMPLE
, new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification(70300)), new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample(<<<'SAMPLE'
<?php

namespace MolliePrefix;

$a = <<<'EOD'
abc
    def
EOD
;

SAMPLE
, new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification(70300), ['indentation' => 'same_as_start'])]);
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return \PHP_VERSION_ID >= 70300 && $tokens->isTokenKindFound(\T_START_HEREDOC);
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('indentation', 'Whether the indentation should be the same as in the start token line or one level more.'))->setAllowedValues(['start_plus_one', 'same_as_start'])->setDefault('start_plus_one')->getOption()]);
    }
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        for ($index = \count($tokens) - 1; 0 <= $index; --$index) {
            if (!$tokens[$index]->isGivenKind(\T_END_HEREDOC)) {
                continue;
            }
            $end = $index;
            $index = $tokens->getPrevTokenOfKind($index, [[\T_START_HEREDOC]]);
            $this->fixIndentation($tokens, $index, $end);
        }
    }
    /**
     * @param int $start
     * @param int $end
     */
    private function fixIndentation(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $start, $end)
    {
        $indent = $this->getIndentAt($tokens, $start);
        if ('start_plus_one' === $this->configuration['indentation']) {
            $indent .= $this->whitespacesConfig->getIndent();
        }
        \MolliePrefix\PhpCsFixer\Preg::match('/^\\h*/', $tokens[$end]->getContent(), $matches);
        $currentIndent = $matches[0];
        $currentIndentLength = \strlen($currentIndent);
        $content = $indent . \substr($tokens[$end]->getContent(), $currentIndentLength);
        $tokens[$end] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_END_HEREDOC, $content]);
        if ($end === $start + 1) {
            return;
        }
        for ($index = $end - 1, $last = \true; $index > $start; --$index, $last = \false) {
            if (!$tokens[$index]->isGivenKind([\T_ENCAPSED_AND_WHITESPACE, \T_WHITESPACE])) {
                continue;
            }
            $content = $tokens[$index]->getContent();
            if ('' !== $currentIndent) {
                $content = \MolliePrefix\PhpCsFixer\Preg::replace('/(?<=\\v)(?!' . $currentIndent . ')\\h+/', '', $content);
            }
            $regexEnd = $last && !$currentIndent ? '(?!\\v|$)' : '(?!\\v)';
            $content = \MolliePrefix\PhpCsFixer\Preg::replace('/(?<=\\v)' . $currentIndent . $regexEnd . '/', $indent, $content);
            $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([$tokens[$index]->getId(), $content]);
        }
        ++$index;
        if (!$tokens[$index]->isGivenKind(\T_ENCAPSED_AND_WHITESPACE)) {
            $tokens->insertAt($index, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_ENCAPSED_AND_WHITESPACE, $indent]));
            return;
        }
        $content = $tokens[$index]->getContent();
        if (!\in_array($content[0], ["\r", "\n"], \true) && (!$currentIndent || $currentIndent === \substr($content, 0, $currentIndentLength))) {
            $content = $indent . \substr($content, $currentIndentLength);
        } elseif ($currentIndent) {
            $content = \MolliePrefix\PhpCsFixer\Preg::replace('/^(?!' . $currentIndent . ')\\h+/', '', $content);
        }
        $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_ENCAPSED_AND_WHITESPACE, $content]);
    }
    /**
     * @param int $index
     *
     * @return string
     */
    private function getIndentAt(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        for (; $index >= 0; --$index) {
            if (!$tokens[$index]->isGivenKind([\T_WHITESPACE, \T_INLINE_HTML, \T_OPEN_TAG])) {
                continue;
            }
            $content = $tokens[$index]->getContent();
            if ($tokens[$index]->isWhitespace() && $tokens[$index - 1]->isGivenKind(\T_OPEN_TAG)) {
                $content = $tokens[$index - 1]->getContent() . $content;
            }
            if (1 === \MolliePrefix\PhpCsFixer\Preg::match('/\\R(\\h*)$/', $content, $matches)) {
                return $matches[1];
            }
        }
        return '';
    }
}
