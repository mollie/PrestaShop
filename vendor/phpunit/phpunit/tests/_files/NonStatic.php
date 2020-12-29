<?php

namespace MolliePrefix;

class NonStatic
{
    public function suite()
    {
        return;
    }
}
\class_alias('MolliePrefix\\NonStatic', 'NonStatic', \false);
