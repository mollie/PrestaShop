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
use MolliePrefix\PhpCsFixer\FixerConfiguration\AllowedValueSubset;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
use MolliePrefix\PhpCsFixer\Tokenizer\TokensAnalyzer;
/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @author Andreas Möller <am@localheinz.com>
 * @author SpacePossum
 */
final class BlankLineBeforeStatementFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface, \MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface
{
    /**
     * @var array
     */
    private static $tokenMap = [
        'break' => \T_BREAK,
        'case' => \T_CASE,
        'continue' => \T_CONTINUE,
        'declare' => \T_DECLARE,
        'default' => \T_DEFAULT,
        'die' => \T_EXIT,
        // TODO remove this alias 3.0, use `exit`
        'do' => \T_DO,
        'exit' => \T_EXIT,
        'for' => \T_FOR,
        'foreach' => \T_FOREACH,
        'goto' => \T_GOTO,
        'if' => \T_IF,
        'include' => \T_INCLUDE,
        'include_once' => \T_INCLUDE_ONCE,
        'require' => \T_REQUIRE,
        'require_once' => \T_REQUIRE_ONCE,
        'return' => \T_RETURN,
        'switch' => \T_SWITCH,
        'throw' => \T_THROW,
        'try' => \T_TRY,
        'while' => \T_WHILE,
        'yield' => \T_YIELD,
    ];
    /**
     * @var array
     */
    private $fixTokenMap = [];
    /**
     * Dynamic yield from option set on constructor.
     */
    public function __construct()
    {
        parent::__construct();
        // To be moved back to compile time property declaration when PHP support of PHP CS Fixer will be 7.0+
        if (\defined('T_YIELD_FROM')) {
            self::$tokenMap['yield_from'] = \T_YIELD_FROM;
        }
    }
    /**
     * {@inheritdoc}
     */
    public function configure(array $configuration = null)
    {
        parent::configure($configuration);
        $this->fixTokenMap = [];
        foreach ($this->configuration['statements'] as $key) {
            if ('die' === $key) {
                @\trigger_error('Option "die" is deprecated, use "exit" instead.', \E_USER_DEPRECATED);
            }
            $this->fixTokenMap[$key] = self::$tokenMap[$key];
        }
        $this->fixTokenMap = \array_values($this->fixTokenMap);
    }
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('An empty line feed must precede any configured statement.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
function A() {
    echo 1;
    return 1;
}
'), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
switch ($foo) {
    case 42:
        $bar->process();
        break;
    case 44:
        break;
}
', ['statements' => ['break']]), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
foreach ($foo as $bar) {
    if ($bar->isTired()) {
        $bar->sleep();
        continue;
    }
}
', ['statements' => ['continue']]), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
$i = 0;
do {
    echo $i;
} while ($i > 0);
', ['statements' => ['do']]), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
if ($foo === false) {
    exit(0);
} else {
    $bar = 9000;
    exit(1);
}
', ['statements' => ['exit']]), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
a:

if ($foo === false) {
    goto a;
} else {
    $bar = 9000;
    goto b;
}
', ['statements' => ['goto']]), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
$a = 9000;
if (true) {
    $foo = $bar;
}
', ['statements' => ['if']]), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php

if (true) {
    $foo = $bar;
    return;
}
', ['statements' => ['return']]), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
$a = 9000;
switch ($a) {
    case 42:
        break;
}
', ['statements' => ['switch']]), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
if (null === $a) {
    $foo->bar();
    throw new \\UnexpectedValueException("A cannot be null.");
}
', ['statements' => ['throw']]), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
$a = 9000;
try {
    $foo->bar();
} catch (\\Exception $exception) {
    $a = -1;
}
', ['statements' => ['try']]), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php

if (true) {
    $foo = $bar;
    yield $foo;
}
', ['statements' => ['yield']])]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run after NoExtraBlankLinesFixer, NoUselessReturnFixer, ReturnAssignmentFixer.
     */
    public function getPriority()
    {
        return -21;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAnyTokenKindsFound($this->fixTokenMap);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $analyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\TokensAnalyzer($tokens);
        for ($index = $tokens->count() - 1; $index > 0; --$index) {
            $token = $tokens[$index];
            if (!$token->isGivenKind($this->fixTokenMap)) {
                continue;
            }
            if ($token->isGivenKind(\T_WHILE) && $analyzer->isWhilePartOfDoWhile($index)) {
                continue;
            }
            $prevNonWhitespace = $tokens->getPrevNonWhitespace($index);
            if ($this->shouldAddBlankLine($tokens, $prevNonWhitespace)) {
                $this->insertBlankLine($tokens, $index);
            }
            $index = $prevNonWhitespace;
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        $allowed = self::$tokenMap;
        $allowed['yield_from'] = \true;
        // TODO remove this when update to PHP7.0
        \ksort($allowed);
        $allowed = \array_keys($allowed);
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('statements', 'List of statements which must be preceded by an empty line.'))->setAllowedTypes(['array'])->setAllowedValues([new \MolliePrefix\PhpCsFixer\FixerConfiguration\AllowedValueSubset($allowed)])->setDefault(['break', 'continue', 'declare', 'return', 'throw', 'try'])->getOption()]);
    }
    /**
     * @param int $prevNonWhitespace
     *
     * @return bool
     */
    private function shouldAddBlankLine(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $prevNonWhitespace)
    {
        $prevNonWhitespaceToken = $tokens[$prevNonWhitespace];
        if ($prevNonWhitespaceToken->isComment()) {
            for ($j = $prevNonWhitespace - 1; $j >= 0; --$j) {
                if (\false !== \strpos($tokens[$j]->getContent(), "\n")) {
                    return \false;
                }
                if ($tokens[$j]->isWhitespace() || $tokens[$j]->isComment()) {
                    continue;
                }
                return $tokens[$j]->equalsAny([';', '}']);
            }
        }
        return $prevNonWhitespaceToken->equalsAny([';', '}']);
    }
    /**
     * @param int $index
     */
    private function insertBlankLine(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        $prevIndex = $index - 1;
        $prevToken = $tokens[$prevIndex];
        $lineEnding = $this->whitespacesConfig->getLineEnding();
        if ($prevToken->isWhitespace()) {
            $newlinesCount = \substr_count($prevToken->getContent(), "\n");
            if (0 === $newlinesCount) {
                $tokens[$prevIndex] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, \rtrim($prevToken->getContent(), " \t") . $lineEnding . $lineEnding]);
            } elseif (1 === $newlinesCount) {
                $tokens[$prevIndex] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, $lineEnding . $prevToken->getContent()]);
            }
        } else {
            $tokens->insertAt($index, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, $lineEnding . $lineEnding]));
        }
    }
}
