<?php

namespace MolliePrefix\DeepCopy\TypeFilter;

/**
 * @final
 */
class ShallowCopyFilter implements \MolliePrefix\DeepCopy\TypeFilter\TypeFilter
{
    /**
     * {@inheritdoc}
     */
    public function apply($element)
    {
        return clone $element;
    }
}
