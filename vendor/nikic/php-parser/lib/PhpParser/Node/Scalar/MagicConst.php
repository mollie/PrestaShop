<?php

namespace MolliePrefix\PhpParser\Node\Scalar;

use MolliePrefix\PhpParser\Node\Scalar;
abstract class MagicConst extends \MolliePrefix\PhpParser\Node\Scalar
{
    /**
     * Constructs a magic constant node.
     *
     * @param array $attributes Additional attributes
     */
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
    }
    public function getSubNodeNames()
    {
        return array();
    }
    /**
     * Get name of magic constant.
     *
     * @return string Name of magic constant
     */
    public abstract function getName();
}
