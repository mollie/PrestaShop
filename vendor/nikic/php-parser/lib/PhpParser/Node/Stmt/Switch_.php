<?php

namespace MolliePrefix\PhpParser\Node\Stmt;

use MolliePrefix\PhpParser\Node;
class Switch_ extends \MolliePrefix\PhpParser\Node\Stmt
{
    /** @var Node\Expr Condition */
    public $cond;
    /** @var Case_[] Case list */
    public $cases;
    /**
     * Constructs a case node.
     *
     * @param Node\Expr $cond       Condition
     * @param Case_[]   $cases      Case list
     * @param array     $attributes Additional attributes
     */
    public function __construct(\MolliePrefix\PhpParser\Node\Expr $cond, array $cases, array $attributes = array())
    {
        parent::__construct($attributes);
        $this->cond = $cond;
        $this->cases = $cases;
    }
    public function getSubNodeNames()
    {
        return array('cond', 'cases');
    }
}
