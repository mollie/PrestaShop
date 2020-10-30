<?php

namespace MolliePrefix;

/**
 * @codeCoverageIgnore
 */
class IgnoreCodeCoverageClass
{
    public function returnTrue()
    {
        return \true;
    }
    public function returnFalse()
    {
        return \false;
    }
}
/**
 * @codeCoverageIgnore
 */
\class_alias('MolliePrefix\\IgnoreCodeCoverageClass', 'MolliePrefix\\IgnoreCodeCoverageClass', \false);
