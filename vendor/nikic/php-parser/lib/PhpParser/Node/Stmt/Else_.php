<?php

namespace MolliePrefix\PhpParser\Node\Stmt;

use MolliePrefix\PhpParser\Node;
class Else_ extends \MolliePrefix\PhpParser\Node\Stmt
{
    /** @var Node[] Statements */
    public $stmts;
    /**
     * Constructs an else node.
     *
     * @param Node[] $stmts      Statements
     * @param array  $attributes Additional attributes
     */
    public function __construct(array $stmts = array(), array $attributes = array())
    {
        parent::__construct($attributes);
        $this->stmts = $stmts;
    }
    public function getSubNodeNames()
    {
        return array('stmts');
    }
}
