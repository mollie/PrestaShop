<?php

namespace MolliePrefix\PhpParser\Node;

use MolliePrefix\PhpParser\NodeAbstract;
class Arg extends \MolliePrefix\PhpParser\NodeAbstract
{
    /** @var Expr Value to pass */
    public $value;
    /** @var bool Whether to pass by ref */
    public $byRef;
    /** @var bool Whether to unpack the argument */
    public $unpack;
    /**
     * Constructs a function call argument node.
     *
     * @param Expr  $value      Value to pass
     * @param bool  $byRef      Whether to pass by ref
     * @param bool  $unpack     Whether to unpack the argument
     * @param array $attributes Additional attributes
     */
    public function __construct(\MolliePrefix\PhpParser\Node\Expr $value, $byRef = \false, $unpack = \false, array $attributes = array())
    {
        parent::__construct($attributes);
        $this->value = $value;
        $this->byRef = $byRef;
        $this->unpack = $unpack;
    }
    public function getSubNodeNames()
    {
        return array('value', 'byRef', 'unpack');
    }
}
