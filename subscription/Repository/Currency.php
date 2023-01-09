<?php

declare(strict_types=1);

namespace Mollie\Subscription\Repository;

class Currency
{
    public function getById(int $id): \Currency
    {
        return new \Currency($id);
    }
}
