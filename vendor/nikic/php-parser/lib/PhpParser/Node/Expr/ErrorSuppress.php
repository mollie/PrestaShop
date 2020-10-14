<?php

namespace MolliePrefix\PhpParser\Node\Expr;

use MolliePrefix\PhpParser\Node\Expr;
class ErrorSuppress extends \MolliePrefix\PhpParser\Node\Expr
{
    /** @var Expr Expression */
    public $expr;
    /**
     * Constructs an error suppress node.
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
