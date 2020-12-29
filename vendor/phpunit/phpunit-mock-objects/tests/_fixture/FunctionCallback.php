<?php

namespace MolliePrefix;

function functionCallback()
{
    $args = \func_get_args();
    if ($args == ['foo', 'bar']) {
        return 'pass';
    }
}
