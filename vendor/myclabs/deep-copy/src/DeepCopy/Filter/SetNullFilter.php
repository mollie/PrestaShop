<?php

namespace MolliePrefix\DeepCopy\Filter;

use MolliePrefix\DeepCopy\Reflection\ReflectionHelper;
/**
 * @final
 */
class SetNullFilter implements \MolliePrefix\DeepCopy\Filter\Filter
{
    /**
     * Sets the object property to null.
     *
     * {@inheritdoc}
     */
    public function apply($object, $property, $objectCopier)
    {
        $reflectionProperty = \MolliePrefix\DeepCopy\Reflection\ReflectionHelper::getProperty($object, $property);
        $reflectionProperty->setAccessible(\true);
        $reflectionProperty->setValue($object, null);
    }
}
