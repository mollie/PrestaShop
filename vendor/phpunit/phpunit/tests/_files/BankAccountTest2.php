<?php

namespace MolliePrefix;

/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use MolliePrefix\PHPUnit\Framework\TestCase;
class BankAccountTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    private $ba;
    protected function setUp()
    {
        $this->ba = new \MolliePrefix\BankAccount();
    }
    public function testBalanceIsInitiallyZero()
    {
        $ba = new \MolliePrefix\BankAccount();
        $balance = $ba->getBalance();
        $this->assertEquals(0, $balance);
    }
    public function testBalanceCannotBecomeNegative()
    {
        try {
            $this->ba->withdrawMoney(1);
        } catch (\MolliePrefix\BankAccountException $e) {
            $this->assertEquals(0, $this->ba->getBalance());
            return;
        }
        $this->fail();
    }
    public function testBalanceCannotBecomeNegative2()
    {
        try {
            $this->ba->depositMoney(-1);
        } catch (\MolliePrefix\BankAccountException $e) {
            $this->assertEquals(0, $this->ba->getBalance());
            return;
        }
        $this->fail();
    }
}
\class_alias('MolliePrefix\\BankAccountTest', 'MolliePrefix\\BankAccountTest', \false);
