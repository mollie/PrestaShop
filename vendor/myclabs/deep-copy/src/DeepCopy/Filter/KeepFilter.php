<?php

namespace MolliePrefix\DeepCopy\Filter;

class KeepFilter implements \MolliePrefix\DeepCopy\Filter\Filter
{
    /**
     * Keeps the value of the object property.
     *
     * {@inheritdoc}
     */
    public function apply($object, $property, $objectCopier)
    {
        // Nothing to do
    }
}
