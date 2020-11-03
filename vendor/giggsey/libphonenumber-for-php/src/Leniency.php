<?php

namespace MolliePrefix\libphonenumber;

use MolliePrefix\libphonenumber\Leniency\Possible;
use MolliePrefix\libphonenumber\Leniency\StrictGrouping;
use MolliePrefix\libphonenumber\Leniency\Valid;
use MolliePrefix\libphonenumber\Leniency\ExactGrouping;
class Leniency
{
    public static function POSSIBLE()
    {
        return new \MolliePrefix\libphonenumber\Leniency\Possible();
    }
    public static function VALID()
    {
        return new \MolliePrefix\libphonenumber\Leniency\Valid();
    }
    public static function STRICT_GROUPING()
    {
        return new \MolliePrefix\libphonenumber\Leniency\StrictGrouping();
    }
    public static function EXACT_GROUPING()
    {
        return new \MolliePrefix\libphonenumber\Leniency\ExactGrouping();
    }
}
