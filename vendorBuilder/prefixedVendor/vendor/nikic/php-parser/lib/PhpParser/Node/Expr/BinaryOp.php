<?php

namespace MolliePrefix\PhpParser\Node\Expr;

use MolliePrefix\PhpParser\Node\Expr;
abstract class BinaryOp extends \MolliePrefix\PhpParser\Node\Expr
{
    /** @var Expr The left hand side expression */
    public $left;
    /** @var Expr The right hand side expression */
    public $right;
    /**
     * Constructs a bitwise and node.
     *
     * @param Expr  $left       The left hand side expression
     * @param Expr  $right      The right hand side expression
     * @param array $attributes Additional attributes
     */
    public function __construct(\MolliePrefix\PhpParser\Node\Expr $left, \MolliePrefix\PhpParser\Node\Expr $right, array $attributes = array())
    {
        parent::__construct($attributes);
        $this->left = $left;
        $this->right = $right;
    }
    public function getSubNodeNames()
    {
        return array('left', 'right');
    }
}
