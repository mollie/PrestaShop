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
namespace MolliePrefix\PhpCsFixer\Differ;

use MolliePrefix\PhpCsFixer\Preg;
use MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter;
/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
final class DiffConsoleFormatter
{
    /**
     * @var bool
     */
    private $isDecoratedOutput;
    /**
     * @var string
     */
    private $template;
    /**
     * @param bool   $isDecoratedOutput
     * @param string $template
     */
    public function __construct($isDecoratedOutput, $template = '%s')
    {
        $this->isDecoratedOutput = $isDecoratedOutput;
        $this->template = $template;
    }
    /**
     * @param string $diff
     * @param string $lineTemplate
     *
     * @return string
     */
    public function format($diff, $lineTemplate = '%s')
    {
        $isDecorated = $this->isDecoratedOutput;
        $template = $isDecorated ? $this->template : \MolliePrefix\PhpCsFixer\Preg::replace('/<[^<>]+>/', '', $this->template);
        return \sprintf($template, \implode(\PHP_EOL, \array_map(static function ($line) use($isDecorated, $lineTemplate) {
            if ($isDecorated) {
                $count = 0;
                $line = \MolliePrefix\PhpCsFixer\Preg::replaceCallback(['/^(\\+.*)/', '/^(\\-.*)/', '/^(@.*)/'], static function ($matches) {
                    if ('+' === $matches[0][0]) {
                        $colour = 'green';
                    } elseif ('-' === $matches[0][0]) {
                        $colour = 'red';
                    } else {
                        $colour = 'cyan';
                    }
                    return \sprintf('<fg=%s>%s</fg=%s>', $colour, \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter::escape($matches[0]), $colour);
                }, $line, 1, $count);
                if (0 === $count) {
                    $line = \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter::escape($line);
                }
            }
            return \sprintf($lineTemplate, $line);
        }, \MolliePrefix\PhpCsFixer\Preg::split('#\\R#u', $diff))));
    }
}
