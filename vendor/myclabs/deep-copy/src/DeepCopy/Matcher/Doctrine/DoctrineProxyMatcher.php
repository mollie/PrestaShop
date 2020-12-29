<?php

namespace MolliePrefix\DeepCopy\Matcher\Doctrine;

use MolliePrefix\DeepCopy\Matcher\Matcher;
use MolliePrefix\Doctrine\Common\Persistence\Proxy;
/**
 * @final
 */
class DoctrineProxyMatcher implements \MolliePrefix\DeepCopy\Matcher\Matcher
{
    /**
     * Matches a Doctrine Proxy class.
     *
     * {@inheritdoc}
     */
    public function matches($object, $property)
    {
        return $object instanceof \MolliePrefix\Doctrine\Common\Persistence\Proxy;
    }
}
