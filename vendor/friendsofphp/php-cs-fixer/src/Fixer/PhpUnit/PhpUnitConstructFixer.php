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
namespace MolliePrefix\PhpCsFixer\Fixer\PhpUnit;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\AllowedValueSubset;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolverRootless;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class PhpUnitConstructFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    private static $assertionFixers = ['assertSame' => 'fixAssertPositive', 'assertEquals' => 'fixAssertPositive', 'assertNotEquals' => 'fixAssertNegative', 'assertNotSame' => 'fixAssertNegative'];
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
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('PHPUnit assertion method calls like `->assertSame(true, $foo)` should be written with dedicated method like `->assertTrue($foo)`.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
$this->assertEquals(false, $b);
$this->assertSame(true, $a);
$this->assertNotEquals(null, $c);
$this->assertNotSame(null, $d);
'), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
$this->assertEquals(false, $b);
$this->assertSame(true, $a);
$this->assertNotEquals(null, $c);
$this->assertNotSame(null, $d);
', ['assertions' => ['assertSame', 'assertNotSame']])], null, 'Fixer could be risky if one is overriding PHPUnit\'s native methods.');
    }
    /**
     * {@inheritdoc}
     *
     * Must run before PhpUnitDedicateAssertFixer.
     */
    public function getPriority()
    {
        return -10;
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        // no assertions to be fixed - fast return
        if (empty($this->configuration['assertions'])) {
            return;
        }
        foreach ($this->configuration['assertions'] as $assertionMethod) {
            $assertionFixer = self::$assertionFixers[$assertionMethod];
            for ($index = 0, $limit = $tokens->count(); $index < $limit; ++$index) {
                $index = $this->{$assertionFixer}($tokens, $index, $assertionMethod);
                if (null === $index) {
                    break;
                }
            }
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolverRootless('assertions', [(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('assertions', 'List of assertion methods to fix.'))->setAllowedTypes(['array'])->setAllowedValues([new \MolliePrefix\PhpCsFixer\FixerConfiguration\AllowedValueSubset(\array_keys(self::$assertionFixers))])->setDefault(['assertEquals', 'assertSame', 'assertNotEquals', 'assertNotSame'])->getOption()], $this->getName());
    }
    /**
     * @param int    $index
     * @param string $method
     *
     * @return null|int
     */
    private function fixAssertNegative(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index, $method)
    {
        static $map = ['false' => 'assertNotFalse', 'null' => 'assertNotNull', 'true' => 'assertNotTrue'];
        return $this->fixAssert($map, $tokens, $index, $method);
    }
    /**
     * @param int    $index
     * @param string $method
     *
     * @return null|int
     */
    private function fixAssertPositive(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index, $method)
    {
        static $map = ['false' => 'assertFalse', 'null' => 'assertNull', 'true' => 'assertTrue'];
        return $this->fixAssert($map, $tokens, $index, $method);
    }
    /**
     * @param array<string, string> $map
     * @param int                   $index
     * @param string                $method
     *
     * @return null|int
     */
    private function fixAssert(array $map, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index, $method)
    {
        $functionsAnalyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer();
        $sequence = $tokens->findSequence([[\T_STRING, $method], '('], $index);
        if (null === $sequence) {
            return null;
        }
        $sequenceIndexes = \array_keys($sequence);
        if (!$functionsAnalyzer->isTheSameClassCall($tokens, $sequenceIndexes[0])) {
            return null;
        }
        $sequenceIndexes[2] = $tokens->getNextMeaningfulToken($sequenceIndexes[1]);
        $firstParameterToken = $tokens[$sequenceIndexes[2]];
        if (!$firstParameterToken->isNativeConstant()) {
            return $sequenceIndexes[2];
        }
        $sequenceIndexes[3] = $tokens->getNextMeaningfulToken($sequenceIndexes[2]);
        // return if first method argument is an expression, not value
        if (!$tokens[$sequenceIndexes[3]]->equals(',')) {
            return $sequenceIndexes[3];
        }
        $tokens[$sequenceIndexes[0]] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_STRING, $map[\strtolower($firstParameterToken->getContent())]]);
        $tokens->clearRange($sequenceIndexes[2], $tokens->getNextNonWhitespace($sequenceIndexes[3]) - 1);
        return $sequenceIndexes[3];
    }
}
