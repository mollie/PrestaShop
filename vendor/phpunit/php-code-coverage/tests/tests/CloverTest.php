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
 * @covers SebastianBergmann\CodeCoverage\Report\Clover
 */
class CloverTest extends \MolliePrefix\SebastianBergmann\CodeCoverage\TestCase
{
    public function testCloverForBankAccountTest()
    {
        $clover = new \MolliePrefix\SebastianBergmann\CodeCoverage\Report\Clover();
        $this->assertStringMatchesFormatFile(TEST_FILES_PATH . 'BankAccount-clover.xml', $clover->process($this->getCoverageForBankAccount(), null, 'BankAccount'));
    }
    public function testCloverForFileWithIgnoredLines()
    {
        $clover = new \MolliePrefix\SebastianBergmann\CodeCoverage\Report\Clover();
        $this->assertStringMatchesFormatFile(TEST_FILES_PATH . 'ignored-lines-clover.xml', $clover->process($this->getCoverageForFileWithIgnoredLines()));
    }
    public function testCloverForClassWithAnonymousFunction()
    {
        $clover = new \MolliePrefix\SebastianBergmann\CodeCoverage\Report\Clover();
        $this->assertStringMatchesFormatFile(TEST_FILES_PATH . 'class-with-anonymous-function-clover.xml', $clover->process($this->getCoverageForClassWithAnonymousFunction()));
    }
}
