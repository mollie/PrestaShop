<?php

namespace MolliePrefix\PhpParser\Node\Expr;

use MolliePrefix\PhpParser\Node\Expr;
class Variable extends \MolliePrefix\PhpParser\Node\Expr
{
    /** @var string|Expr Name */
    public $name;
    /**
     * Constructs a variable node.
     *
     * @param string|Expr $name       Name
     * @param array                      $attributes Additional attributes
     */
    public function __construct($name, array $attributes = array())
    {
        parent::__construct($attributes);
        $this->name = $name;
    }
    public function getSubNodeNames()
    {
        return array('name');
    }
}
