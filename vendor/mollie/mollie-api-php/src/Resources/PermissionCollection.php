<?php

namespace MolliePrefix\Mollie\Api\Resources;

class PermissionCollection extends \MolliePrefix\Mollie\Api\Resources\BaseCollection
{
    /**
     * @return string
     */
    public function getCollectionResourceName()
    {
        return "permissions";
    }
}
