<?php

namespace MolliePrefix\PhpParser\Node\Expr;

use MolliePrefix\PhpParser\Node\Expr;
use MolliePrefix\PhpParser\Node\Name;
class ConstFetch extends \MolliePrefix\PhpParser\Node\Expr
{
    /** @var Name Constant name */
    public $name;
    /**
     * Constructs a const fetch node.
     *
     * @param Name  $name       Constant name
     * @param array $attributes Additional attributes
     */
    public function __construct(\MolliePrefix\PhpParser\Node\Name $name, array $attributes = array())
    {
        parent::__construct($attributes);
        $this->name = $name;
    }
    public function getSubNodeNames()
    {
        return array('name');
    }
}
