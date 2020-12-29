<?php

namespace MolliePrefix\DeepCopy\Filter\Doctrine;

use MolliePrefix\DeepCopy\Filter\Filter;
/**
 * @final
 */
class DoctrineProxyFilter implements \MolliePrefix\DeepCopy\Filter\Filter
{
    /**
     * Triggers the magic method __load() on a Doctrine Proxy class to load the
     * actual entity from the database.
     *
     * {@inheritdoc}
     */
    public function apply($object, $property, $objectCopier)
    {
        $object->__load();
    }
}
