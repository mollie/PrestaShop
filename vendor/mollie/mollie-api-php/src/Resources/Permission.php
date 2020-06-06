<?php

namespace _PhpScoper5ea00cc67502b\Mollie\Api\Resources;

class Permission extends \_PhpScoper5ea00cc67502b\Mollie\Api\Resources\BaseResource
{
    /**
     * @var string
     */
    public $resource;
    /**
     * @var string
     * @example payments.read
     */
    public $id;
    /**
     * @var string
     */
    public $description;
    /**
     * @var bool
     */
    public $granted;
    /**
     * @var \stdClass
     */
    public $_links;
}
