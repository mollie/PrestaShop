<?php

namespace Mollie\Repository;

final class PendingOrderCartRepository extends AbstractRepository
{
    /**
     * @param int $prestaOrderId
     * @return bool
     * @throws \PrestaShopException
     */
    public function isPendingCancellableOrder($prestaOrderId)
    {
        return null !== $this->findOneBy([
            'order_id' => (int) $prestaOrderId,
            'should_cancel_order' => 1,
        ]);
    }
}