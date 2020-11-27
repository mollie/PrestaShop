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
namespace MolliePrefix\PhpCsFixer\Fixer\StringNotation;

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
 * @author Gregor Harlan <gharlan@web.de>
 */
final class SingleQuoteFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        $codeSample = <<<'EOF'
<?php

namespace MolliePrefix;

$a = "sample";
$b = "sample with 'single-quotes'";

EOF;
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Convert double quotes to single quotes for simple strings.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample($codeSample), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample($codeSample, ['strings_containing_single_quote_chars' => \true])]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run after BacktickToShellExecFixer, EscapeImplicitBackslashesFixer.
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
        return $tokens->isTokenKindFound(\T_CONSTANT_ENCAPSED_STRING);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind(\T_CONSTANT_ENCAPSED_STRING)) {
                continue;
            }
            $content = $token->getContent();
            $prefix = '';
            if ('b' === \strtolower($content[0])) {
                $prefix = $content[0];
                $content = \substr($content, 1);
            }
            if ('"' === $content[0] && (\true === $this->configuration['strings_containing_single_quote_chars'] || \false === \strpos($content, "'")) && !\MolliePrefix\PhpCsFixer\Preg::match('/(?<!\\\\)(?:\\\\{2})*\\\\(?!["$\\\\])/', $content)) {
                $content = \substr($content, 1, -1);
                $content = \str_replace(['\\"', '\\$', '\''], ['"', '$', '\\\''], $content);
                $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_CONSTANT_ENCAPSED_STRING, $prefix . '\'' . $content . '\'']);
            }
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('strings_containing_single_quote_chars', 'Whether to fix double-quoted strings that contains single-quotes.'))->setAllowedTypes(['bool'])->setDefault(\false)->getOption()]);
    }
}
