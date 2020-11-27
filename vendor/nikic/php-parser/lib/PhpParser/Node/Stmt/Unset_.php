<?php

namespace MolliePrefix\PhpParser\Node\Stmt;

use MolliePrefix\PhpParser\Node;
class Unset_ extends \MolliePrefix\PhpParser\Node\Stmt
{
    /** @var Node\Expr[] Variables to unset */
    public $vars;
    /**
     * Constructs an unset node.
     *
     * @param Node\Expr[] $vars       Variables to unset
     * @param array       $attributes Additional attributes
     */
    public function __construct(array $vars, array $attributes = array())
    {
        parent::__construct($attributes);
        $this->vars = $vars;
    }
    public function getSubNodeNames()
    {
        return array('vars');
    }
}
