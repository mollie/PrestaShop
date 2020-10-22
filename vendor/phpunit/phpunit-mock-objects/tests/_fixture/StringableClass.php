<?php

namespace MolliePrefix;

class StringableClass
{
    public function __toString()
    {
        return '12345';
    }
}
\class_alias('MolliePrefix\\StringableClass', 'StringableClass', \false);
