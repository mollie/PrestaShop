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
namespace MolliePrefix\PhpCsFixer;

use MolliePrefix\PhpCsFixer\DocBlock\Annotation;
use MolliePrefix\PhpCsFixer\DocBlock\DocBlock;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * This abstract fixer provides a base for fixers to fix types in PHPDoc.
 *
 * @author Graham Campbell <graham@alt-three.com>
 *
 * @internal
 */
abstract class AbstractPhpdocTypesFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * The annotation tags search inside.
     *
     * @var string[]
     */
    protected $tags;
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();
        $this->tags = \MolliePrefix\PhpCsFixer\DocBlock\Annotation::getTagsWithTypes();
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isTokenKindFound(\T_DOC_COMMENT);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind(\T_DOC_COMMENT)) {
                continue;
            }
            $doc = new \MolliePrefix\PhpCsFixer\DocBlock\DocBlock($token->getContent());
            $annotations = $doc->getAnnotationsOfType($this->tags);
            if (empty($annotations)) {
                continue;
            }
            foreach ($annotations as $annotation) {
                $this->fixTypes($annotation);
            }
            $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_DOC_COMMENT, $doc->getContent()]);
        }
    }
    /**
     * Actually normalize the given type.
     *
     * @param string $type
     *
     * @return string
     */
    protected abstract function normalize($type);
    /**
     * Fix the types at the given line.
     *
     * We must be super careful not to modify parts of words.
     *
     * This will be nicely handled behind the scenes for us by the annotation class.
     */
    private function fixTypes(\MolliePrefix\PhpCsFixer\DocBlock\Annotation $annotation)
    {
        $types = $annotation->getTypes();
        $new = $this->normalizeTypes($types);
        if ($types !== $new) {
            $annotation->setTypes($new);
        }
    }
    /**
     * @param string[] $types
     *
     * @return string[]
     */
    private function normalizeTypes(array $types)
    {
        foreach ($types as $index => $type) {
            $types[$index] = $this->normalizeType($type);
        }
        return $types;
    }
    /**
     * Prepare the type and normalize it.
     *
     * @param string $type
     *
     * @return string
     */
    private function normalizeType($type)
    {
        if ('[]' === \substr($type, -2)) {
            return $this->normalizeType(\substr($type, 0, -2)) . '[]';
        }
        return $this->normalize($type);
    }
}
