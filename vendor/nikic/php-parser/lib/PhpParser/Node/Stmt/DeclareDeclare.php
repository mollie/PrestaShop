<?php

namespace MolliePrefix\PhpParser\Node\Stmt;

use MolliePrefix\PhpParser\Node;
class DeclareDeclare extends \MolliePrefix\PhpParser\Node\Stmt
{
    /** @var string Key */
    public $key;
    /** @var Node\Expr Value */
    public $value;
    /**
     * Constructs a declare key=>value pair node.
     *
     * @param string    $key        Key
     * @param Node\Expr $value      Value
     * @param array     $attributes Additional attributes
     */
    public function __construct($key, \MolliePrefix\PhpParser\Node\Expr $value, array $attributes = array())
    {
        parent::__construct($attributes);
        $this->key = $key;
        $this->value = $value;
    }
    public function getSubNodeNames()
    {
        return array('key', 'value');
    }
}
