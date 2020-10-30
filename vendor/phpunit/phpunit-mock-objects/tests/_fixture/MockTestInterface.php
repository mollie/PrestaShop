<?php

namespace MolliePrefix;

interface MockTestInterface
{
    public function returnAnything();
    public function returnAnythingElse();
}
\class_alias('MolliePrefix\\MockTestInterface', 'MolliePrefix\\MockTestInterface', \false);
