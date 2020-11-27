<?php

namespace MolliePrefix\PhpParser\Node\Expr;

use MolliePrefix\PhpParser\Node\Expr;
class UnaryMinus extends \MolliePrefix\PhpParser\Node\Expr
{
    /** @var Expr Expression */
    public $expr;
    /**
     * Constructs a unary minus node.
     *
     * @param Expr  $expr       Expression
     * @param array $attributes Additional attributes
     */
    public function __construct(\MolliePrefix\PhpParser\Node\Expr $expr, array $attributes = array())
    {
        parent::__construct($attributes);
        $this->expr = $expr;
    }
    public function getSubNodeNames()
    {
        return array('expr');
    }
}
