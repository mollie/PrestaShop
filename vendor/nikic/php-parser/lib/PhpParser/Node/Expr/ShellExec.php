<?php

namespace MolliePrefix\PhpParser\Node\Expr;

use MolliePrefix\PhpParser\Node\Expr;
class ShellExec extends \MolliePrefix\PhpParser\Node\Expr
{
    /** @var array Encapsed string array */
    public $parts;
    /**
     * Constructs a shell exec (backtick) node.
     *
     * @param array $parts      Encapsed string array
     * @param array $attributes Additional attributes
     */
    public function __construct(array $parts, array $attributes = array())
    {
        parent::__construct($attributes);
        $this->parts = $parts;
    }
    public function getSubNodeNames()
    {
        return array('parts');
    }
}
