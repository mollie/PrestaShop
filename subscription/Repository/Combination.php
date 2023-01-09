<?php

declare(strict_types=1);

namespace Mollie\Subscription\Repository;

class Combination
{
    public function getById(int $id): \Combination
    {
        return new \Combination($id);
    }
}
