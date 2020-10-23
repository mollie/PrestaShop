<?php

/*
 * This file is part of the php-code-coverage package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\SebastianBergmann\CodeCoverage\Report;

use MolliePrefix\SebastianBergmann\CodeCoverage\TestCase;
/**
 * @covers SebastianBergmann\CodeCoverage\Report\Text
 */
class TextTest extends \MolliePrefix\SebastianBergmann\CodeCoverage\TestCase
{
    public function testTextForBankAccountTest()
    {
        $text = new \MolliePrefix\SebastianBergmann\CodeCoverage\Report\Text(50, 90, \false, \false);
        $this->assertStringMatchesFormatFile(TEST_FILES_PATH . 'BankAccount-text.txt', \str_replace(\PHP_EOL, "\n", $text->process($this->getCoverageForBankAccount())));
    }
    public function testTextForFileWithIgnoredLines()
    {
        $text = new \MolliePrefix\SebastianBergmann\CodeCoverage\Report\Text(50, 90, \false, \false);
        $this->assertStringMatchesFormatFile(TEST_FILES_PATH . 'ignored-lines-text.txt', \str_replace(\PHP_EOL, "\n", $text->process($this->getCoverageForFileWithIgnoredLines())));
    }
    public function testTextForClassWithAnonymousFunction()
    {
        $text = new \MolliePrefix\SebastianBergmann\CodeCoverage\Report\Text(50, 90, \false, \false);
        $this->assertStringMatchesFormatFile(TEST_FILES_PATH . 'class-with-anonymous-function-text.txt', \str_replace(\PHP_EOL, "\n", $text->process($this->getCoverageForClassWithAnonymousFunction())));
    }
}
