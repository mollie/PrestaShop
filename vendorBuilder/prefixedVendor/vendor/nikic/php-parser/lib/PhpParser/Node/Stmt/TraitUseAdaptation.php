<?php

namespace MolliePrefix\PhpParser\Node\Stmt;

use MolliePrefix\PhpParser\Node;
abstract class TraitUseAdaptation extends \MolliePrefix\PhpParser\Node\Stmt
{
    /** @var Node\Name Trait name */
    public $trait;
    /** @var string Method name */
    public $method;
}
