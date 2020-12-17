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
namespace MolliePrefix\PhpCsFixer\Fixer\ConstantNotation;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceAnalysis;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\NamespacesAnalyzer;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\NamespaceUsesAnalyzer;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
use MolliePrefix\PhpCsFixer\Tokenizer\TokensAnalyzer;
use MolliePrefix\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
/**
 * @author Filippo Tessarotto <zoeslam@gmail.com>
 */
final class NativeConstantInvocationFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    /**
     * @var array<string, true>
     */
    private $constantsToEscape = [];
    /**
     * @var array<string, true>
     */
    private $caseInsensitiveConstantsToEscape = [];
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Add leading `\\` before constant invocation of internal constant to speed up resolving. Constant name match is case-sensitive, except for `null`, `false` and `true`.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php var_dump(PHP_VERSION, M_PI, MY_CUSTOM_PI);\n"), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
namespace space1 {
    echo PHP_VERSION;
}
namespace {
    echo M_PI;
}
', ['scope' => 'namespaced']), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php var_dump(PHP_VERSION, M_PI, MY_CUSTOM_PI);\n", ['include' => ['MY_CUSTOM_PI']]), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php var_dump(PHP_VERSION, M_PI, MY_CUSTOM_PI);\n", ['fix_built_in' => \false, 'include' => ['MY_CUSTOM_PI']]), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php var_dump(PHP_VERSION, M_PI, MY_CUSTOM_PI);\n", ['exclude' => ['M_PI']])], null, 'Risky when any of the constants are namespaced or overridden.');
    }
    /**
     * {@inheritdoc}
     *
     * Must run before GlobalNamespaceImportFixer.
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
        return $tokens->isTokenKindFound(\T_STRING);
    }
    /**
     * {@inheritdoc}
     */
    public function isRisky()
    {
        return \true;
    }
    /**
     * {@inheritdoc}
     */
    public function configure(array $configuration = null)
    {
        parent::configure($configuration);
        $uniqueConfiguredExclude = \array_unique($this->configuration['exclude']);
        // Case sensitive constants handling
        $constantsToEscape = \array_values($this->configuration['include']);
        if (\true === $this->configuration['fix_built_in']) {
            $getDefinedConstants = \get_defined_constants(\true);
            unset($getDefinedConstants['user']);
            foreach ($getDefinedConstants as $constants) {
                $constantsToEscape = \array_merge($constantsToEscape, \array_keys($constants));
            }
        }
        $constantsToEscape = \array_diff(\array_unique($constantsToEscape), $uniqueConfiguredExclude);
        // Case insensitive constants handling
        static $caseInsensitiveConstants = ['null', 'false', 'true'];
        $caseInsensitiveConstantsToEscape = [];
        foreach ($constantsToEscape as $constantIndex => $constant) {
            $loweredConstant = \strtolower($constant);
            if (\in_array($loweredConstant, $caseInsensitiveConstants, \true)) {
                $caseInsensitiveConstantsToEscape[] = $loweredConstant;
                unset($constantsToEscape[$constantIndex]);
            }
        }
        $caseInsensitiveConstantsToEscape = \array_diff(\array_unique($caseInsensitiveConstantsToEscape), \array_map(static function ($function) {
            return \strtolower($function);
        }, $uniqueConfiguredExclude));
        // Store the cache
        $this->constantsToEscape = \array_fill_keys($constantsToEscape, \true);
        \ksort($this->constantsToEscape);
        $this->caseInsensitiveConstantsToEscape = \array_fill_keys($caseInsensitiveConstantsToEscape, \true);
        \ksort($this->caseInsensitiveConstantsToEscape);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        if ('all' === $this->configuration['scope']) {
            $this->fixConstantInvocations($tokens, 0, \count($tokens) - 1);
            return;
        }
        $namespaces = (new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\NamespacesAnalyzer())->getDeclarations($tokens);
        // 'scope' is 'namespaced' here
        /** @var NamespaceAnalysis $namespace */
        foreach (\array_reverse($namespaces) as $namespace) {
            if ('' === $namespace->getFullName()) {
                continue;
            }
            $this->fixConstantInvocations($tokens, $namespace->getScopeStartIndex(), $namespace->getScopeEndIndex());
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        $constantChecker = static function ($value) {
            foreach ($value as $constantName) {
                if (!\is_string($constantName) || '' === \trim($constantName) || \trim($constantName) !== $constantName) {
                    throw new \MolliePrefix\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException(\sprintf('Each element must be a non-empty, trimmed string, got "%s" instead.', \is_object($constantName) ? \get_class($constantName) : \gettype($constantName)));
                }
            }
            return \true;
        };
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('fix_built_in', 'Whether to fix constants returned by `get_defined_constants`. User constants are not accounted in this list and must be specified in the include one.'))->setAllowedTypes(['bool'])->setDefault(\true)->getOption(), (new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('include', 'List of additional constants to fix.'))->setAllowedTypes(['array'])->setAllowedValues([$constantChecker])->setDefault([])->getOption(), (new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('exclude', 'List of constants to ignore.'))->setAllowedTypes(['array'])->setAllowedValues([$constantChecker])->setDefault(['null', 'false', 'true'])->getOption(), (new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('scope', 'Only fix constant invocations that are made within a namespace or fix all.'))->setAllowedValues(['all', 'namespaced'])->setDefault('all')->getOption(), (new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('strict', 'Whether leading `\\` of constant invocation not meant to have it should be removed.'))->setAllowedTypes(['bool'])->setDefault(\false)->getOption()]);
    }
    /**
     * @param int $startIndex
     * @param int $endIndex
     */
    private function fixConstantInvocations(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $startIndex, $endIndex)
    {
        $useDeclarations = (new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\NamespaceUsesAnalyzer())->getDeclarationsFromTokens($tokens);
        $useConstantDeclarations = [];
        foreach ($useDeclarations as $use) {
            if ($use->isConstant()) {
                $useConstantDeclarations[$use->getShortName()] = \true;
            }
        }
        $tokenAnalyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\TokensAnalyzer($tokens);
        for ($index = $endIndex; $index > $startIndex; --$index) {
            $token = $tokens[$index];
            // test if we are at a constant call
            if (!$token->isGivenKind(\T_STRING)) {
                continue;
            }
            if (!$tokenAnalyzer->isConstantInvocation($index)) {
                continue;
            }
            $tokenContent = $token->getContent();
            $prevIndex = $tokens->getPrevMeaningfulToken($index);
            if (!isset($this->constantsToEscape[$tokenContent]) && !isset($this->caseInsensitiveConstantsToEscape[\strtolower($tokenContent)])) {
                if (!$this->configuration['strict']) {
                    continue;
                }
                if (!$tokens[$prevIndex]->isGivenKind(\T_NS_SEPARATOR)) {
                    continue;
                }
                $prevPrevIndex = $tokens->getPrevMeaningfulToken($prevIndex);
                if ($tokens[$prevPrevIndex]->isGivenKind(\T_STRING)) {
                    continue;
                }
                $tokens->clearTokenAndMergeSurroundingWhitespace($prevIndex);
                continue;
            }
            if (isset($useConstantDeclarations[$tokenContent])) {
                continue;
            }
            if ($tokens[$prevIndex]->isGivenKind(\T_NS_SEPARATOR)) {
                continue;
            }
            $tokens->insertAt($index, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_NS_SEPARATOR, '\\']));
        }
    }
}
