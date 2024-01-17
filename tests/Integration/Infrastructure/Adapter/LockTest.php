<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Tests\Integration\Infrastructure\Adapter;

use Mollie\Exception\Code\ExceptionCode;
use Mollie\Infrastructure\Adapter\Lock;
use Mollie\Infrastructure\Exception\CouldNotHandleLocking;
use Mollie\Tests\Integration\BaseTestCase;

class LockTest extends BaseTestCase
{
    public function testItSuccessfullyCompletesLockFlow(): void
    {
        /** @var Lock $lock */
        $lock = $this->getService(Lock::class);

        $lock->create('test-lock-name');

        $this->assertTrue($lock->acquire());

        $lock->release();
    }

    public function testItSuccessfullyLocksResourceFromAnotherProcess(): void
    {
        /** @var Lock $lock */
        $lock = $this->getService(Lock::class);

        $lock->create('test-lock-name');

        $this->assertTrue($lock->acquire());

        /** @var Lock $newLock */
        $newLock = $this->getService(Lock::class);

        $newLock->create('test-lock-name');

        $this->assertFalse($newLock->acquire());
    }

    public function testItUnsuccessfullyCompletesLockFlowFailedToCreateLockWithMissingLock(): void
    {
        /** @var Lock $lock */
        $lock = $this->getService(Lock::class);

        $this->expectException(CouldNotHandleLocking::class);
        $this->expectExceptionCode(ExceptionCode::INFRASTRUCTURE_LOCK_EXISTS);

        $lock->create('test-lock-name');
        $lock->create('test-lock-name');
    }

    public function testItUnsuccessfullyCompletesLockFlowFailedToAcquireLockWithMissingLock(): void
    {
        /** @var Lock $lock */
        $lock = $this->getService(Lock::class);

        $this->expectException(CouldNotHandleLocking::class);
        $this->expectExceptionCode(ExceptionCode::INFRASTRUCTURE_LOCK_ON_ACQUIRE_IS_MISSING);

        $lock->acquire();
    }

    public function testItUnsuccessfullyCompletesLockFlowFailedToReleaseLockWithMissingLock(): void
    {
        /** @var Lock $lock */
        $lock = $this->getService(Lock::class);

        $this->expectException(CouldNotHandleLocking::class);
        $this->expectExceptionCode(ExceptionCode::INFRASTRUCTURE_LOCK_ON_RELEASE_IS_MISSING);

        $lock->release();
    }
}
