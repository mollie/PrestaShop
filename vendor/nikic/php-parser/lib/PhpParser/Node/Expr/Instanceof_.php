<?php

namespace MolliePrefix\PhpParser\Node\Expr;

use MolliePrefix\PhpParser\Node\Expr;
use MolliePrefix\PhpParser\Node\Name;
class Instanceof_ extends \MolliePrefix\PhpParser\Node\Expr
{
    /** @var Expr Expression */
    public $expr;
    /** @var Name|Expr Class name */
    public $class;
    /**
     * Constructs an instanceof check node.
     *
     * @param Expr      $expr       Expression
     * @param Name|Expr $class      Class name
     * @param array     $attributes Additional attributes
     */
    public function __construct(\MolliePrefix\PhpParser\Node\Expr $expr, $class, array $attributes = array())
    {
        parent::__construct($attributes);
        $this->expr = $expr;
        $this->class = $class;
    }
    public function getSubNodeNames()
    {
        return array('expr', 'class');
    }
}
