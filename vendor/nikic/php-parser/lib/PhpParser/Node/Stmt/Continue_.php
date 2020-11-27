<?php

namespace MolliePrefix\PhpParser\Node\Stmt;

use MolliePrefix\PhpParser\Node;
class Continue_ extends \MolliePrefix\PhpParser\Node\Stmt
{
    /** @var null|Node\Expr Number of loops to continue */
    public $num;
    /**
     * Constructs a continue node.
     *
     * @param null|Node\Expr $num        Number of loops to continue
     * @param array          $attributes Additional attributes
     */
    public function __construct(\MolliePrefix\PhpParser\Node\Expr $num = null, array $attributes = array())
    {
        parent::__construct($attributes);
        $this->num = $num;
    }
    public function getSubNodeNames()
    {
        return array('num');
    }
}
