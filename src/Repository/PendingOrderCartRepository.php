<?php

namespace Mollie\Repository;

final class PendingOrderCartRepository extends AbstractRepository
{
    /**
     * @param int $prestaCartId
     * @return bool
     * @throws \PrestaShopException
     */
    public function hasPendingCancellableOrder($prestaCartId)
    {
        return null !== $this->findOneBy([
            'cart_id' => (int) $prestaCartId,
            'should_cancel_order' => 1,
        ]);
    }
}