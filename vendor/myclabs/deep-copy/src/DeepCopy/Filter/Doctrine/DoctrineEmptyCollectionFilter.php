<?php

namespace MolliePrefix\DeepCopy\Filter\Doctrine;

use MolliePrefix\DeepCopy\Filter\Filter;
use MolliePrefix\DeepCopy\Reflection\ReflectionHelper;
use MolliePrefix\Doctrine\Common\Collections\ArrayCollection;
/**
 * @final
 */
class DoctrineEmptyCollectionFilter implements \MolliePrefix\DeepCopy\Filter\Filter
{
    /**
     * Sets the object property to an empty doctrine collection.
     *
     * @param object   $object
     * @param string   $property
     * @param callable $objectCopier
     */
    public function apply($object, $property, $objectCopier)
    {
        $reflectionProperty = \MolliePrefix\DeepCopy\Reflection\ReflectionHelper::getProperty($object, $property);
        $reflectionProperty->setAccessible(\true);
        $reflectionProperty->setValue($object, new \MolliePrefix\Doctrine\Common\Collections\ArrayCollection());
    }
}
