<?php

namespace MolliePrefix\PhpParser\Node\Stmt;

use MolliePrefix\PhpParser\Node;
class Declare_ extends \MolliePrefix\PhpParser\Node\Stmt
{
    /** @var DeclareDeclare[] List of declares */
    public $declares;
    /** @var Node[] Statements */
    public $stmts;
    /**
     * Constructs a declare node.
     *
     * @param DeclareDeclare[] $declares   List of declares
     * @param Node[]|null      $stmts      Statements
     * @param array            $attributes Additional attributes
     */
    public function __construct(array $declares, array $stmts = null, array $attributes = array())
    {
        parent::__construct($attributes);
        $this->declares = $declares;
        $this->stmts = $stmts;
    }
    public function getSubNodeNames()
    {
        return array('declares', 'stmts');
    }
}
