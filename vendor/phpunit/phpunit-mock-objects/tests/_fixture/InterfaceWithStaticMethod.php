<?php

namespace MolliePrefix;

interface InterfaceWithStaticMethod
{
    public static function staticMethod();
}
\class_alias('MolliePrefix\\InterfaceWithStaticMethod', 'MolliePrefix\\InterfaceWithStaticMethod', \false);
