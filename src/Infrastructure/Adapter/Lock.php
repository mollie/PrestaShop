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

namespace Mollie\Infrastructure\Adapter;

use Mollie\Config\Config;
use Mollie\Infrastructure\Exception\CouldNotHandleLocking;
use Symfony\Component\Lock\Factory as LockFactoryV3;
use Symfony\Component\Lock\LockFactory as LockFactoryV4;
use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Lock\Store\FlockStore;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Lock
{
    private $lockFactory;
    /** @var ?LockInterface */
    private $lock;

    public function __construct()
    {
        $store = new FlockStore();

        if (class_exists(LockFactoryV4::class)) {
            // Symfony 4.4+
            $this->lockFactory = new LockFactoryV4($store);

            return;
        }

        // Symfony 3.4+
        $this->lockFactory = new LockFactoryV3($store);
    }

    /**
     * @throws CouldNotHandleLocking
     */
    public function create(string $resource, int $ttl = Config::LOCK_TIME_TO_LIVE, bool $autoRelease = true): void
    {
        if ($this->lock) {
            throw CouldNotHandleLocking::lockExists();
        }

        $this->lock = $this->lockFactory->createLock($resource, $ttl, $autoRelease);
    }

    /**
     * @throws CouldNotHandleLocking
     */
    public function acquire(bool $blocking = false): bool
    {
        if (!$this->lock) {
            throw CouldNotHandleLocking::lockOnAcquireIsMissing();
        }

        return $this->lock->acquire($blocking);
    }

    /**
     * @throws CouldNotHandleLocking
     */
    public function release(): void
    {
        if (!$this->lock) {
            throw CouldNotHandleLocking::lockOnReleaseIsMissing();
        }

        $this->lock->release();

        $this->lock = null;
    }

    public function __destruct()
    {
        try {
            $this->release();
        } catch (CouldNotHandleLocking $exception) {
            return;
        }
    }
}
