<?php

namespace MolliePrefix\DeepCopy\f001;

class B extends \MolliePrefix\DeepCopy\f001\A
{
    private $bProp;
    public function getBProp()
    {
        return $this->bProp;
    }
    public function setBProp($prop)
    {
        $this->bProp = $prop;
        return $this;
    }
}
