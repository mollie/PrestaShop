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
namespace MolliePrefix\PhpCsFixer\Fixer\Basic;

use MolliePrefix\PhpCsFixer\AbstractPsrAutoloadingFixer;
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\FileSpecificCodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @author Bram Gotink <bram@gotink.me>
 * @author Graham Campbell <graham@alt-three.com>
 */
final class Psr0Fixer extends \MolliePrefix\PhpCsFixer\AbstractPsrAutoloadingFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Classes must be in a path that matches their namespace, be at least one namespace deep and the class name should match the file name.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\FileSpecificCodeSample('<?php
namespace PhpCsFixer\\FIXER\\Basic;
class InvalidName {}
', new \SplFileInfo(__FILE__)), new \MolliePrefix\PhpCsFixer\FixerDefinition\FileSpecificCodeSample('<?php
namespace PhpCsFixer\\FIXER\\Basic;
class InvalidName {}
', new \SplFileInfo(__FILE__), ['dir' => \realpath(__DIR__ . '/../..')])], null, 'This fixer may change your class name, which will break the code that depends on the old name.');
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $namespace = \false;
        $namespaceIndex = 0;
        $namespaceEndIndex = 0;
        $classyName = null;
        $classyIndex = 0;
        foreach ($tokens as $index => $token) {
            if ($token->isGivenKind(\T_NAMESPACE)) {
                if (\false !== $namespace) {
                    return;
                }
                $namespaceIndex = $tokens->getNextMeaningfulToken($index);
                $namespaceEndIndex = $tokens->getNextTokenOfKind($index, [';']);
                $namespace = \trim($tokens->generatePartialCode($namespaceIndex, $namespaceEndIndex - 1));
            } elseif ($token->isClassy()) {
                $prevToken = $tokens[$tokens->getPrevMeaningfulToken($index)];
                if ($prevToken->isGivenKind(\T_NEW)) {
                    continue;
                }
                if (null !== $classyName) {
                    return;
                }
                $classyIndex = $tokens->getNextMeaningfulToken($index);
                $classyName = $tokens[$classyIndex]->getContent();
            }
        }
        if (null === $classyName) {
            return;
        }
        if (\false !== $namespace) {
            $normNamespace = \str_replace('\\', '/', $namespace);
            $path = \str_replace('\\', '/', $file->getRealPath());
            $dir = \dirname($path);
            if ('' !== $this->configuration['dir']) {
                /** @var false|string $dir until support for PHP 5.6 is dropped */
                $dir = \substr($dir, \strlen(\realpath($this->configuration['dir'])) + 1);
                if (\false === $dir) {
                    $dir = '';
                }
                if (\strlen($normNamespace) > \strlen($dir)) {
                    if ('' !== $dir) {
                        $normNamespace = \substr($normNamespace, -\strlen($dir));
                    } else {
                        $normNamespace = '';
                    }
                }
            }
            /** @var false|string $dir until support for PHP 5.6 is dropped */
            $dir = \substr($dir, -\strlen($normNamespace));
            if (\false === $dir) {
                $dir = '';
            }
            $filename = \basename($path, '.php');
            if ($classyName !== $filename) {
                $tokens[$classyIndex] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_STRING, $filename]);
            }
            if ($normNamespace !== $dir && \strtolower($normNamespace) === \strtolower($dir)) {
                for ($i = $namespaceIndex; $i <= $namespaceEndIndex; ++$i) {
                    $tokens->clearAt($i);
                }
                $namespace = \substr($namespace, 0, -\strlen($dir)) . \str_replace('/', '\\', $dir);
                $newNamespace = \MolliePrefix\PhpCsFixer\Tokenizer\Tokens::fromCode('<?php namespace ' . $namespace . ';');
                $newNamespace->clearRange(0, 2);
                $newNamespace->clearEmptyTokens();
                $tokens->insertAt($namespaceIndex, $newNamespace);
            }
        } else {
            $normClass = \str_replace('_', '/', $classyName);
            $path = \str_replace('\\', '/', $file->getRealPath());
            $filename = \substr($path, -\strlen($normClass) - 4, -4);
            if ($normClass !== $filename && \strtolower($normClass) === \strtolower($filename)) {
                $tokens[$classyIndex] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_STRING, \str_replace('/', '_', $filename)]);
            }
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('dir', 'The directory where the project code is placed.'))->setAllowedTypes(['string'])->setDefault('')->getOption()]);
    }
}
