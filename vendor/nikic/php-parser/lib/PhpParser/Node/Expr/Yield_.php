<?php

namespace MolliePrefix\PhpParser\Node\Expr;

use MolliePrefix\PhpParser\Node\Expr;
class Yield_ extends \MolliePrefix\PhpParser\Node\Expr
{
    /** @var null|Expr Key expression */
    public $key;
    /** @var null|Expr Value expression */
    public $value;
    /**
     * Constructs a yield expression node.
     *
     * @param null|Expr $value      Value expression
     * @param null|Expr $key        Key expression
     * @param array     $attributes Additional attributes
     */
    public function __construct(\MolliePrefix\PhpParser\Node\Expr $value = null, \MolliePrefix\PhpParser\Node\Expr $key = null, array $attributes = array())
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
