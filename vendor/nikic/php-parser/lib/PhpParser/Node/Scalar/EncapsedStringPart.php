<?php

namespace MolliePrefix\PhpParser\Node\Scalar;

use MolliePrefix\PhpParser\Node\Scalar;
class EncapsedStringPart extends \MolliePrefix\PhpParser\Node\Scalar
{
    /** @var string String value */
    public $value;
    /**
     * Constructs a node representing a string part of an encapsed string.
     *
     * @param string $value      String value
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
