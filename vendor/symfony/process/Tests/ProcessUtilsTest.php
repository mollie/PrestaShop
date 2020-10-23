<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Process\Tests;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Process\ProcessUtils;
/**
 * @group legacy
 */
class ProcessUtilsTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider dataArguments
     */
    public function testEscapeArgument($result, $argument)
    {
        $this->assertSame($result, \MolliePrefix\Symfony\Component\Process\ProcessUtils::escapeArgument($argument));
    }
    public function dataArguments()
    {
        if ('\\' === \DIRECTORY_SEPARATOR) {
            return [['"\\"php\\" \\"-v\\""', '"php" "-v"'], ['"foo bar"', 'foo bar'], ['^%"path"^%', '%path%'], ['"<|>\\" \\"\'f"', '<|>" "\'f'], ['""', ''], ['"with\\trailingbs\\\\"', 'with\\trailingbs\\']];
        }
        return [["'\"php\" \"-v\"'", '"php" "-v"'], ["'foo bar'", 'foo bar'], ["'%path%'", '%path%'], ["'<|>\" \"'\\''f'", '<|>" "\'f'], ["''", ''], ["'with\\trailingbs\\'", 'with\\trailingbs\\'], ["'withNonAsciiAccentLikeéÉèÈàÀöä'", 'withNonAsciiAccentLikeéÉèÈàÀöä']];
    }
}
