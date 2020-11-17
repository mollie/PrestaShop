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
namespace MolliePrefix\PhpCsFixer\Fixer\Strict;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification;
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author SpacePossum
 */
final class DeclareStrictTypesFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Force strict types declaration in all files. Requires PHP >= 7.0.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample("<?php\n", new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification(70000))], null, 'Forcing strict types will stop non strict code from working.');
    }
    /**
     * {@inheritdoc}
     *
     * Must run before BlankLineAfterOpeningTagFixer, DeclareEqualNormalizeFixer, HeaderCommentFixer.
     */
    public function getPriority()
    {
        return 2;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return \PHP_VERSION_ID >= 70000 && isset($tokens[0]) && $tokens[0]->isGivenKind(\T_OPEN_TAG);
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
        // check if the declaration is already done
        $searchIndex = $tokens->getNextMeaningfulToken(0);
        if (null === $searchIndex) {
            $this->insertSequence($tokens);
            // declaration not found, insert one
            return;
        }
        $sequenceLocation = $tokens->findSequence([[\T_DECLARE, 'declare'], '(', [\T_STRING, 'strict_types'], '=', [\T_LNUMBER], ')'], $searchIndex, null, \false);
        if (null === $sequenceLocation) {
            $this->insertSequence($tokens);
            // declaration not found, insert one
            return;
        }
        $this->fixStrictTypesCasingAndValue($tokens, $sequenceLocation);
    }
    /**
     * @param array<int, Token> $sequence
     */
    private function fixStrictTypesCasingAndValue(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, array $sequence)
    {
        /** @var int $index */
        /** @var Token $token */
        foreach ($sequence as $index => $token) {
            if ($token->isGivenKind(\T_STRING)) {
                $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_STRING, \strtolower($token->getContent())]);
                continue;
            }
            if ($token->isGivenKind(\T_LNUMBER)) {
                $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_LNUMBER, '1']);
                break;
            }
        }
    }
    private function insertSequence(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $sequence = [new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_DECLARE, 'declare']), new \MolliePrefix\PhpCsFixer\Tokenizer\Token('('), new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_STRING, 'strict_types']), new \MolliePrefix\PhpCsFixer\Tokenizer\Token('='), new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_LNUMBER, '1']), new \MolliePrefix\PhpCsFixer\Tokenizer\Token(')'), new \MolliePrefix\PhpCsFixer\Tokenizer\Token(';')];
        $endIndex = \count($sequence);
        $tokens->insertAt(1, $sequence);
        // start index of the sequence is always 1 here, 0 is always open tag
        // transform "<?php\n" to "<?php " if needed
        if (\false !== \strpos($tokens[0]->getContent(), "\n")) {
            $tokens[0] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([$tokens[0]->getId(), \trim($tokens[0]->getContent()) . ' ']);
        }
        if ($endIndex === \count($tokens) - 1) {
            return;
            // no more tokens afters sequence, single_blank_line_at_eof might add a line
        }
        $lineEnding = $this->whitespacesConfig->getLineEnding();
        if (!$tokens[1 + $endIndex]->isWhitespace()) {
            $tokens->insertAt(1 + $endIndex, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, $lineEnding]));
            return;
        }
        $content = $tokens[1 + $endIndex]->getContent();
        $tokens[1 + $endIndex] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, $lineEnding . \ltrim($content, " \t")]);
    }
}
