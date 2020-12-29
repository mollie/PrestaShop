<?php

namespace MolliePrefix\DeepCopy\Filter\Doctrine;

use MolliePrefix\DeepCopy\Filter\Filter;
use MolliePrefix\DeepCopy\Reflection\ReflectionHelper;
/**
 * @final
 */
class DoctrineCollectionFilter implements \MolliePrefix\DeepCopy\Filter\Filter
{
    /**
     * Copies the object property doctrine collection.
     *
     * {@inheritdoc}
     */
    public function apply($object, $property, $objectCopier)
    {
        $reflectionProperty = \MolliePrefix\DeepCopy\Reflection\ReflectionHelper::getProperty($object, $property);
        $reflectionProperty->setAccessible(\true);
        $oldCollection = $reflectionProperty->getValue($object);
        $newCollection = $oldCollection->map(function ($item) use($objectCopier) {
            return $objectCopier($item);
        });
        $reflectionProperty->setValue($object, $newCollection);
    }
}
