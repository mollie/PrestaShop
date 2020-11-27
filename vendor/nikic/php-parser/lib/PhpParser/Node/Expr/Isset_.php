<?php

namespace MolliePrefix\PhpParser\Node\Expr;

use MolliePrefix\PhpParser\Node\Expr;
class Isset_ extends \MolliePrefix\PhpParser\Node\Expr
{
    /** @var Expr[] Variables */
    public $vars;
    /**
     * Constructs an array node.
     *
     * @param Expr[] $vars       Variables
     * @param array  $attributes Additional attributes
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
