<?php

namespace MolliePrefix\PhpParser\Node\Expr;

use MolliePrefix\PhpParser\Node\Expr;
class ArrayDimFetch extends \MolliePrefix\PhpParser\Node\Expr
{
    /** @var Expr Variable */
    public $var;
    /** @var null|Expr Array index / dim */
    public $dim;
    /**
     * Constructs an array index fetch node.
     *
     * @param Expr      $var        Variable
     * @param null|Expr $dim        Array index / dim
     * @param array     $attributes Additional attributes
     */
    public function __construct(\MolliePrefix\PhpParser\Node\Expr $var, \MolliePrefix\PhpParser\Node\Expr $dim = null, array $attributes = array())
    {
        parent::__construct($attributes);
        $this->var = $var;
        $this->dim = $dim;
    }
    public function getSubNodeNames()
    {
        return array('var', 'dim');
    }
}
