<?php

namespace _PhpScoper5ea00cc67502b\Mollie\Api\Resources;

use ArrayObject;
use stdClass;

abstract class BaseCollection extends ArrayObject
{
    /**
     * Total number of retrieved objects.
     *
     * @var int
     */
    public $count;
    /**
     * @var stdClass
     */
    public $_links;
    /**
     * @param int $count
     * @param stdClass $_links
     */
    public function __construct($count, $_links)
    {
        $this->count = $count;
        $this->_links = $_links;
        parent::__construct();
    }
    /**
     * @return string|null
     */
    public abstract function getCollectionResourceName();
}
