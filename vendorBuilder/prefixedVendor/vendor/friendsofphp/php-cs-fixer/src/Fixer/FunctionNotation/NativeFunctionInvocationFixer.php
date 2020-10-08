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
namespace MolliePrefix\PhpCsFixer\Fixer\FunctionNotation;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceAnalysis;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\NamespacesAnalyzer;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
use MolliePrefix\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
/**
 * @author Andreas Möller <am@localheinz.com>
 * @author SpacePossum
 */
final class NativeFunctionInvocationFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    /**
     * @internal
     */
    const SET_ALL = '@all';
    /**
     * Subset of SET_INTERNAL.
     *
     * Change function call to functions known to be optimized by the Zend engine.
     * For details:
     * - @see https://github.com/php/php-src/blob/php-7.2.6/Zend/zend_compile.c "zend_try_compile_special_func"
     * - @see https://github.com/php/php-src/blob/php-7.2.6/ext/opcache/Optimizer/pass1_5.c
     *
     * @internal
     */
    const SET_COMPILER_OPTIMIZED = '@compiler_optimized';
    /**
     * @internal
     */
    const SET_INTERNAL = '@internal';
    /**
     * @var callable
     */
    private $functionFilter;
    public function configure(array $configuration = null)
    {
        parent::configure($configuration);
        $this->functionFilter = $this->getFunctionFilter();
    }
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Add leading `\\` before function invocation to speed up resolving.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php

function baz($options)
{
    if (!array_key_exists("foo", $options)) {
        throw new \\InvalidArgumentException();
    }

    return json_encode($options);
}
'), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php

function baz($options)
{
    if (!array_key_exists("foo", $options)) {
        throw new \\InvalidArgumentException();
    }

    return json_encode($options);
}
', ['exclude' => ['json_encode']]), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
namespace space1 {
    echo count([1]);
}
namespace {
    echo count([1]);
}
', ['scope' => 'all']), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
namespace space1 {
    echo count([1]);
}
namespace {
    echo count([1]);
}
', ['scope' => 'namespaced']), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
myGlobalFunction();
count();
', ['include' => ['myGlobalFunction']]), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
myGlobalFunction();
count();
', ['include' => ['@all']]), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
myGlobalFunction();
count();
', ['include' => ['@internal']]), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
$a .= str_repeat($a, 4);
$c = get_class($d);
', ['include' => ['@compiler_optimized']])], null, 'Risky when any of the functions are overridden.');
    }
    /**
     * {@inheritdoc}
     *
     * Must run before GlobalNamespaceImportFixer.
     * Must run after StrictParamFixer.
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
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        if ('all' === $this->configuration['scope']) {
            $this->fixFunctionCalls($tokens, $this->functionFilter, 0, \count($tokens) - 1, \false);
            return;
        }
        $namespaces = (new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\NamespacesAnalyzer())->getDeclarations($tokens);
        // 'scope' is 'namespaced' here
        /** @var NamespaceAnalysis $namespace */
        foreach (\array_reverse($namespaces) as $namespace) {
            $this->fixFunctionCalls($tokens, $this->functionFilter, $namespace->getScopeStartIndex(), $namespace->getScopeEndIndex(), '' === $namespace->getFullName());
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('exclude', 'List of functions to ignore.'))->setAllowedTypes(['array'])->setAllowedValues([static function (array $value) {
            foreach ($value as $functionName) {
                if (!\is_string($functionName) || '' === \trim($functionName) || \trim($functionName) !== $functionName) {
                    throw new \MolliePrefix\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException(\sprintf('Each element must be a non-empty, trimmed string, got "%s" instead.', \is_object($functionName) ? \get_class($functionName) : \gettype($functionName)));
                }
            }
            return \true;
        }])->setDefault([])->getOption(), (new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('include', 'List of function names or sets to fix. Defined sets are `@internal` (all native functions), `@all` (all global functions) and `@compiler_optimized` (functions that are specially optimized by Zend).'))->setAllowedTypes(['array'])->setAllowedValues([static function (array $value) {
            foreach ($value as $functionName) {
                if (!\is_string($functionName) || '' === \trim($functionName) || \trim($functionName) !== $functionName) {
                    throw new \MolliePrefix\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException(\sprintf('Each element must be a non-empty, trimmed string, got "%s" instead.', \is_object($functionName) ? \get_class($functionName) : \gettype($functionName)));
                }
                $sets = [self::SET_ALL, self::SET_INTERNAL, self::SET_COMPILER_OPTIMIZED];
                if ('@' === $functionName[0] && !\in_array($functionName, $sets, \true)) {
                    throw new \MolliePrefix\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException(\sprintf('Unknown set "%s", known sets are "%s".', $functionName, \implode('", "', $sets)));
                }
            }
            return \true;
        }])->setDefault([self::SET_INTERNAL])->getOption(), (new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('scope', 'Only fix function calls that are made within a namespace or fix all.'))->setAllowedValues(['all', 'namespaced'])->setDefault('all')->getOption(), (new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('strict', 'Whether leading `\\` of function call not meant to have it should be removed.'))->setAllowedTypes(['bool'])->setDefault(\false)->getOption()]);
    }
    /**
     * @param int  $start
     * @param int  $end
     * @param bool $tryToRemove
     */
    private function fixFunctionCalls(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, callable $functionFilter, $start, $end, $tryToRemove)
    {
        $functionsAnalyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer();
        $insertAtIndexes = [];
        for ($index = $start; $index < $end; ++$index) {
            if (!$functionsAnalyzer->isGlobalFunctionCall($tokens, $index)) {
                continue;
            }
            $prevIndex = $tokens->getPrevMeaningfulToken($index);
            if (!$functionFilter($tokens[$index]->getContent()) || $tryToRemove) {
                if (!$this->configuration['strict']) {
                    continue;
                }
                if ($tokens[$prevIndex]->isGivenKind(\T_NS_SEPARATOR)) {
                    $tokens->clearTokenAndMergeSurroundingWhitespace($prevIndex);
                }
                continue;
            }
            if ($tokens[$prevIndex]->isGivenKind(\T_NS_SEPARATOR)) {
                continue;
                // do not bother if previous token is already namespace separator
            }
            $insertAtIndexes[] = $index;
        }
        foreach (\array_reverse($insertAtIndexes) as $index) {
            $tokens->insertAt($index, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_NS_SEPARATOR, '\\']));
        }
    }
    /**
     * @return callable
     */
    private function getFunctionFilter()
    {
        $exclude = $this->normalizeFunctionNames($this->configuration['exclude']);
        if (\in_array(self::SET_ALL, $this->configuration['include'], \true)) {
            if (\count($exclude) > 0) {
                return static function ($functionName) use($exclude) {
                    return !isset($exclude[\strtolower($functionName)]);
                };
            }
            return static function () {
                return \true;
            };
        }
        $include = [];
        if (\in_array(self::SET_INTERNAL, $this->configuration['include'], \true)) {
            $include = $this->getAllInternalFunctionsNormalized();
        } elseif (\in_array(self::SET_COMPILER_OPTIMIZED, $this->configuration['include'], \true)) {
            $include = $this->getAllCompilerOptimizedFunctionsNormalized();
            // if `@internal` is set all compiler optimized function are already loaded
        }
        foreach ($this->configuration['include'] as $additional) {
            if ('@' !== $additional[0]) {
                $include[\strtolower($additional)] = \true;
            }
        }
        if (\count($exclude) > 0) {
            return static function ($functionName) use($include, $exclude) {
                return isset($include[\strtolower($functionName)]) && !isset($exclude[\strtolower($functionName)]);
            };
        }
        return static function ($functionName) use($include) {
            return isset($include[\strtolower($functionName)]);
        };
    }
    /**
     * @return array<string, true> normalized function names of which the PHP compiler optimizes
     */
    private function getAllCompilerOptimizedFunctionsNormalized()
    {
        return $this->normalizeFunctionNames([
            // @see https://github.com/php/php-src/blob/PHP-7.4/Zend/zend_compile.c "zend_try_compile_special_func"
            'array_key_exists',
            'array_slice',
            'assert',
            'boolval',
            'call_user_func',
            'call_user_func_array',
            'chr',
            'count',
            'defined',
            'doubleval',
            'floatval',
            'func_get_args',
            'func_num_args',
            'get_called_class',
            'get_class',
            'gettype',
            'in_array',
            'intval',
            'is_array',
            'is_bool',
            'is_double',
            'is_float',
            'is_int',
            'is_integer',
            'is_long',
            'is_null',
            'is_object',
            'is_real',
            'is_resource',
            'is_string',
            'ord',
            'strlen',
            'strval',
            // @see https://github.com/php/php-src/blob/php-7.2.6/ext/opcache/Optimizer/pass1_5.c
            'constant',
            'define',
            'dirname',
            'extension_loaded',
            'function_exists',
            'is_callable',
        ]);
    }
    /**
     * @return array<string, true> normalized function names of all internal defined functions
     */
    private function getAllInternalFunctionsNormalized()
    {
        return $this->normalizeFunctionNames(\get_defined_functions()['internal']);
    }
    /**
     * @param string[] $functionNames
     *
     * @return array<string, true> all function names lower cased
     */
    private function normalizeFunctionNames(array $functionNames)
    {
        foreach ($functionNames as $index => $functionName) {
            $functionNames[\strtolower($functionName)] = \true;
            unset($functionNames[$index]);
        }
        return $functionNames;
    }
}
