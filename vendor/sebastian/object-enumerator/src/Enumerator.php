<?php

/*
 * This file is part of Object Enumerator.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\SebastianBergmann\ObjectEnumerator;

use MolliePrefix\SebastianBergmann\RecursionContext\Context;
/**
 * Traverses array structures and object graphs
 * to enumerate all referenced objects.
 */
class Enumerator
{
    /**
     * Returns an array of all objects referenced either
     * directly or indirectly by a variable.
     *
     * @param array|object $variable
     *
     * @return object[]
     */
    public function enumerate($variable)
    {
        if (!\is_array($variable) && !\is_object($variable)) {
            throw new \MolliePrefix\SebastianBergmann\ObjectEnumerator\InvalidArgumentException();
        }
        if (isset(\func_get_args()[1])) {
            if (!\func_get_args()[1] instanceof \MolliePrefix\SebastianBergmann\RecursionContext\Context) {
                throw new \MolliePrefix\SebastianBergmann\ObjectEnumerator\InvalidArgumentException();
            }
            $processed = \func_get_args()[1];
        } else {
            $processed = new \MolliePrefix\SebastianBergmann\RecursionContext\Context();
        }
        $objects = [];
        if ($processed->contains($variable)) {
            return $objects;
        }
        $array = $variable;
        $processed->add($variable);
        if (\is_array($variable)) {
            foreach ($array as $element) {
                if (!\is_array($element) && !\is_object($element)) {
                    continue;
                }
                $objects = \array_merge($objects, $this->enumerate($element, $processed));
            }
        } else {
            $objects[] = $variable;
            $reflector = new \ReflectionObject($variable);
            foreach ($reflector->getProperties() as $attribute) {
                $attribute->setAccessible(\true);
                try {
                    $value = $attribute->getValue($variable);
                } catch (\Throwable $e) {
                    continue;
                } catch (\Exception $e) {
                    continue;
                }
                if (!\is_array($value) && !\is_object($value)) {
                    continue;
                }
                $objects = \array_merge($objects, $this->enumerate($value, $processed));
            }
        }
        return $objects;
    }
}
