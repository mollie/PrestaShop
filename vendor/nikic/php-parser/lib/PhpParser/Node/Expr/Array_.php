<?php

namespace MolliePrefix\PhpParser\Node\Expr;

use MolliePrefix\PhpParser\Node\Expr;
class Array_ extends \MolliePrefix\PhpParser\Node\Expr
{
    // For use in "kind" attribute
    const KIND_LONG = 1;
    // array() syntax
    const KIND_SHORT = 2;
    // [] syntax
    /** @var ArrayItem[] Items */
    public $items;
    /**
     * Constructs an array node.
     *
     * @param ArrayItem[] $items      Items of the array
     * @param array       $attributes Additional attributes
     */
    public function __construct(array $items = array(), array $attributes = array())
    {
        parent::__construct($attributes);
        $this->items = $items;
    }
    public function getSubNodeNames()
    {
        return array('items');
    }
}
