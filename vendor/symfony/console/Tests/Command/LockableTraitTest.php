<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Console\Tests\Command;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Console\Tester\CommandTester;
use MolliePrefix\Symfony\Component\Lock\Factory;
use MolliePrefix\Symfony\Component\Lock\Store\FlockStore;
use MolliePrefix\Symfony\Component\Lock\Store\SemaphoreStore;
class LockableTraitTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    protected static $fixturesPath;
    public static function setUpBeforeClass()
    {
        self::$fixturesPath = __DIR__ . '/../Fixtures/';
        require_once self::$fixturesPath . '/FooLockCommand.php';
        require_once self::$fixturesPath . '/FooLock2Command.php';
    }
    public function testLockIsReleased()
    {
        $command = new \MolliePrefix\FooLockCommand();
        $tester = new \MolliePrefix\Symfony\Component\Console\Tester\CommandTester($command);
        $this->assertSame(2, $tester->execute([]));
        $this->assertSame(2, $tester->execute([]));
    }
    public function testLockReturnsFalseIfAlreadyLockedByAnotherCommand()
    {
        $command = new \MolliePrefix\FooLockCommand();
        if (\MolliePrefix\Symfony\Component\Lock\Store\SemaphoreStore::isSupported(\false)) {
            $store = new \MolliePrefix\Symfony\Component\Lock\Store\SemaphoreStore();
        } else {
            $store = new \MolliePrefix\Symfony\Component\Lock\Store\FlockStore();
        }
        $lock = (new \MolliePrefix\Symfony\Component\Lock\Factory($store))->createLock($command->getName());
        $lock->acquire();
        $tester = new \MolliePrefix\Symfony\Component\Console\Tester\CommandTester($command);
        $this->assertSame(1, $tester->execute([]));
        $lock->release();
        $this->assertSame(2, $tester->execute([]));
    }
    public function testMultipleLockCallsThrowLogicException()
    {
        $command = new \MolliePrefix\FooLock2Command();
        $tester = new \MolliePrefix\Symfony\Component\Console\Tester\CommandTester($command);
        $this->assertSame(1, $tester->execute([]));
    }
}
