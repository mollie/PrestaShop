<?php

namespace MolliePrefix\PhpParser\Node\Stmt;

use MolliePrefix\PhpParser\Node;
class Throw_ extends \MolliePrefix\PhpParser\Node\Stmt
{
    /** @var Node\Expr Expression */
    public $expr;
    /**
     * Constructs a throw node.
     *
     * @param Node\Expr $expr       Expression
     * @param array     $attributes Additional attributes
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
