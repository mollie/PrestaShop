<?php

declare(strict_types=1);

namespace Mollie\Subscription\Repository;

class CombinationRepository
{
    public function getById(int $id): \Combination
    {
        return new \Combination($id);
    }
}
