<?php

namespace MolliePrefix\PhpParser\Node\Stmt;

use MolliePrefix\PhpParser\Node\Stmt;
class InlineHTML extends \MolliePrefix\PhpParser\Node\Stmt
{
    /** @var string String */
    public $value;
    /**
     * Constructs an inline HTML node.
     *
     * @param string $value      String
     * @param array  $attributes Additional attributes
     */
    public function __construct($value, array $attributes = array())
    {
        parent::__construct($attributes);
        $this->value = $value;
    }
    public function getSubNodeNames()
    {
        return array('value');
    }
}
