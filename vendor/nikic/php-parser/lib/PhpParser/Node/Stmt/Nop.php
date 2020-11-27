<?php

namespace MolliePrefix\PhpParser\Node\Stmt;

use MolliePrefix\PhpParser\Node;
/** Nop/empty statement (;). */
class Nop extends \MolliePrefix\PhpParser\Node\Stmt
{
    public function getSubNodeNames()
    {
        return array();
    }
}
