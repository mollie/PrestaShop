<?php

namespace Mollie\Repository;

class AddressFormatRepository extends AbstractRepository implements AddressFormatRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(\AddressFormat::class);
    }
}
