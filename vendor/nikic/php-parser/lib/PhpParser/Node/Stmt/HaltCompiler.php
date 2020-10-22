<?php

namespace MolliePrefix\PhpParser\Node\Stmt;

use MolliePrefix\PhpParser\Node\Stmt;
class HaltCompiler extends \MolliePrefix\PhpParser\Node\Stmt
{
    /** @var string Remaining text after halt compiler statement. */
    public $remaining;
    /**
     * Constructs a __halt_compiler node.
     *
     * @param string $remaining  Remaining text after halt compiler statement.
     * @param array  $attributes Additional attributes
     */
    public function __construct($remaining, array $attributes = array())
    {
        parent::__construct($attributes);
        $this->remaining = $remaining;
    }
    public function getSubNodeNames()
    {
        return array('remaining');
    }
}
