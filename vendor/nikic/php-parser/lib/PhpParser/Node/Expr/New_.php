<?php

namespace MolliePrefix\PhpParser\Node\Expr;

use MolliePrefix\PhpParser\Node;
use MolliePrefix\PhpParser\Node\Expr;
class New_ extends \MolliePrefix\PhpParser\Node\Expr
{
    /** @var Node\Name|Expr|Node\Stmt\Class_ Class name */
    public $class;
    /** @var Node\Arg[] Arguments */
    public $args;
    /**
     * Constructs a function call node.
     *
     * @param Node\Name|Expr|Node\Stmt\Class_ $class      Class name (or class node for anonymous classes)
     * @param Node\Arg[]                      $args       Arguments
     * @param array                           $attributes Additional attributes
     */
    public function __construct($class, array $args = array(), array $attributes = array())
    {
        parent::__construct($attributes);
        $this->class = $class;
        $this->args = $args;
    }
    public function getSubNodeNames()
    {
        return array('class', 'args');
    }
}
