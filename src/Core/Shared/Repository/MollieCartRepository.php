<?php

namespace Mollie\Core\Shared\Repository;

use Mollie\Repository\AbstractRepository;

class MollieCartRepository extends AbstractRepository implements MollieCartRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(\MollieCart::class);
    }
}
