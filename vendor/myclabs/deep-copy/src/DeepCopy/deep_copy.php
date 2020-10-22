<?php

namespace MolliePrefix\DeepCopy;

/**
 * Deep copies the given value.
 *
 * @param mixed $value
 * @param bool  $useCloneMethod
 *
 * @return mixed
 */
function deep_copy($value, $useCloneMethod = \false)
{
    return (new \MolliePrefix\DeepCopy\DeepCopy($useCloneMethod))->copy($value);
}
