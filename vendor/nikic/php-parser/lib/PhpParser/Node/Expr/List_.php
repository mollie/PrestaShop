<?php

namespace MolliePrefix\PhpParser\Node\Expr;

use MolliePrefix\PhpParser\Node\Expr;
class List_ extends \MolliePrefix\PhpParser\Node\Expr
{
    /** @var ArrayItem[] List of items to assign to */
    public $items;
    /**
     * Constructs a list() destructuring node.
     *
     * @param ArrayItem[] $items      List of items to assign to
     * @param array       $attributes Additional attributes
     */
    public function __construct(array $items, array $attributes = array())
    {
        parent::__construct($attributes);
        $this->items = $items;
    }
    public function getSubNodeNames()
    {
        return array('items');
    }
}
