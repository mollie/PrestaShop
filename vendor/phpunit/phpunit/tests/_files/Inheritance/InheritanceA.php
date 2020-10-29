<?php

namespace MolliePrefix;

require_once __DIR__ . '/InheritanceB.php';
class InheritanceA extends \MolliePrefix\InheritanceB
{
}
\class_alias('MolliePrefix\\InheritanceA', 'MolliePrefix\\InheritanceA', \false);
