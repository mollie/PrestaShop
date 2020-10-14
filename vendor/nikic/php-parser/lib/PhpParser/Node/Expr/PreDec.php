<?php

namespace MolliePrefix\PhpParser\Node\Expr;

use MolliePrefix\PhpParser\Node\Expr;
class PreDec extends \MolliePrefix\PhpParser\Node\Expr
{
    /** @var Expr Variable */
    public $var;
    /**
     * Constructs a pre decrement node.
     *
     * @param Expr  $var        Variable
     * @param array $attributes Additional attributes
     */
    public function __construct(\MolliePrefix\PhpParser\Node\Expr $var, array $attributes = array())
    {
        parent::__construct($attributes);
        $this->var = $var;
    }
    public function getSubNodeNames()
    {
        return array('var');
    }
}
