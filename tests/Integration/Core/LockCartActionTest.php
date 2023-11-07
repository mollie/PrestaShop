<?php

namespace Mollie\Tests\Integration\Core;

use Mollie\Core\Payment\Action\LockCartAction;
use Mollie\Tests\Integration\BaseTestCase;

class LockCartActionTest extends BaseTestCase
{
    public function testItSuccessfullyLocksCart(): void
    {
        /** @var LockCartAction $lockCartAction */
        $lockCartAction = $this->getService(LockCartAction::class);

        $this->assertDatabaseHasNot(\MollieCart::class, [
            'id_cart' => 1,
            'id_shop' => (int) $this->contextBuilder->getContext()->shop->id,
        ]);

        $lockCartAction->run(1);

        $this->assertDatabaseHas(\MollieCart::class, [
            'id_cart' => 1,
            'id_shop' => (int) $this->contextBuilder->getContext()->shop->id,
        ]);
    }
}
