<?php

namespace MolliePrefix\Mollie\Api\Resources;

class MethodCollection extends \MolliePrefix\Mollie\Api\Resources\BaseCollection
{
    /**
     * @return string
     */
    public function getCollectionResourceName()
    {
        return "methods";
    }
}
