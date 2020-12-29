<?php

namespace MolliePrefix;

interface InterfaceWithSemiReservedMethodName
{
    public function unset();
}
\class_alias('MolliePrefix\\InterfaceWithSemiReservedMethodName', 'InterfaceWithSemiReservedMethodName', \false);
