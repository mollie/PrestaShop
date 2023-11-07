<?php

namespace Mollie\Tests\Integration\Core;

use Mollie\Core\Payment\Action\UnlockCartAction;
use Mollie\Tests\Integration\BaseTestCase;

class UnlockCartActionTest extends BaseTestCase
{
    public function testItSuccessfullyLocksCart(): void
    {
        $mollieCart = new \MollieCart();

        $mollieCart->id_cart = 1;
        $mollieCart->id_shop = (int) $this->contextBuilder->getContext()->shop->id;

        $mollieCart->save();

        /** @var UnlockCartAction $unlockCartAction */
        $unlockCartAction = $this->getService(UnlockCartAction::class);

        $this->assertDatabaseHas(\MollieCart::class, [
            'id_cart' => 1,
            'id_shop' => (int) $this->contextBuilder->getContext()->shop->id,
        ]);

        $unlockCartAction->run(1);

        $this->assertDatabaseHasNot(\MollieCart::class, [
            'id_cart' => 1,
            'id_shop' => (int) $this->contextBuilder->getContext()->shop->id,
        ]);
    }

    public function testItUnsuccessfullyLocksCartNoCartFound(): void
    {
        /** @var UnlockCartAction $unlockCartAction */
        $unlockCartAction = $this->getService(UnlockCartAction::class);

        $this->assertDatabaseHasNot(\MollieCart::class, [
            'id_cart' => 1,
            'id_shop' => (int) $this->contextBuilder->getContext()->shop->id,
        ]);

        $unlockCartAction->run(1);

        $this->assertDatabaseHasNot(\MollieCart::class, [
            'id_cart' => 1,
            'id_shop' => (int) $this->contextBuilder->getContext()->shop->id,
        ]);
    }
}
