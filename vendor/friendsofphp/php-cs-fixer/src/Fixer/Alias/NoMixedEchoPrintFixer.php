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
namespace MolliePrefix\PhpCsFixer\Fixer\Alias;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\CT;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 * @author SpacePossum
 */
final class NoMixedEchoPrintFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    /**
     * @deprecated will be removed in 3.0
     */
    public static $defaultConfig = ['use' => 'echo'];
    /**
     * @var string
     */
    private $callBack;
    /**
     * @var int T_ECHO or T_PRINT
     */
    private $candidateTokenType;
    /**
     * {@inheritdoc}
     */
    public function configure(array $configuration = null)
    {
        parent::configure($configuration);
        if ('echo' === $this->configuration['use']) {
            $this->candidateTokenType = \T_PRINT;
            $this->callBack = 'fixPrintToEcho';
        } else {
            $this->candidateTokenType = \T_ECHO;
            $this->callBack = 'fixEchoToPrint';
        }
    }
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Either language construct `print` or `echo` should be used.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php print 'example';\n"), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php echo('example');\n", ['use' => 'print'])]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run after EchoTagSyntaxFixer, NoShortEchoTagFixer.
     */
    public function getPriority()
    {
        return -10;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isTokenKindFound($this->candidateTokenType);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $callBack = $this->callBack;
        foreach ($tokens as $index => $token) {
            if ($token->isGivenKind($this->candidateTokenType)) {
                $this->{$callBack}($tokens, $index);
            }
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('use', 'The desired language construct.'))->setAllowedValues(['print', 'echo'])->setDefault('echo')->getOption()]);
    }
    /**
     * @param int $index
     */
    private function fixEchoToPrint(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        $nextTokenIndex = $tokens->getNextMeaningfulToken($index);
        $endTokenIndex = $tokens->getNextTokenOfKind($index, [';', [\T_CLOSE_TAG]]);
        $canBeConverted = \true;
        for ($i = $nextTokenIndex; $i < $endTokenIndex; ++$i) {
            if ($tokens[$i]->equalsAny(['(', [\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_ARRAY_SQUARE_BRACE_OPEN]])) {
                $blockType = \MolliePrefix\PhpCsFixer\Tokenizer\Tokens::detectBlockType($tokens[$i]);
                $i = $tokens->findBlockEnd($blockType['type'], $i);
            }
            if ($tokens[$i]->equals(',')) {
                $canBeConverted = \false;
                break;
            }
        }
        if (\false === $canBeConverted) {
            return;
        }
        $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_PRINT, 'print']);
    }
    /**
     * @param int $index
     */
    private function fixPrintToEcho(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        $prevToken = $tokens[$tokens->getPrevMeaningfulToken($index)];
        if (!$prevToken->equalsAny([';', '{', '}', [\T_OPEN_TAG]])) {
            return;
        }
        $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_ECHO, 'echo']);
    }
}
