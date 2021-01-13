<?php

namespace MolliePrefix\PhpParser\Node\Expr;

use MolliePrefix\PhpParser\Node\Expr;
class PropertyFetch extends \MolliePrefix\PhpParser\Node\Expr
{
    /** @var Expr Variable holding object */
    public $var;
    /** @var string|Expr Property name */
    public $name;
    /**
     * Constructs a function call node.
     *
     * @param Expr        $var        Variable holding object
     * @param string|Expr $name       Property name
     * @param array       $attributes Additional attributes
     */
    public function __construct(\MolliePrefix\PhpParser\Node\Expr $var, $name, array $attributes = array())
    {
        parent::__construct($attributes);
        $this->var = $var;
        $this->name = $name;
    }
    public function getSubNodeNames()
    {
        return array('var', 'name');
    }
}
