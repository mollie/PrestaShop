<?php

namespace MolliePrefix\PhpParser\Node\Stmt;

use MolliePrefix\PhpParser\Node\Stmt;
class Static_ extends \MolliePrefix\PhpParser\Node\Stmt
{
    /** @var StaticVar[] Variable definitions */
    public $vars;
    /**
     * Constructs a static variables list node.
     *
     * @param StaticVar[] $vars       Variable definitions
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
