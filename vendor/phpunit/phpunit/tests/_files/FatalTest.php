<?php

namespace MolliePrefix;

class FatalTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testFatalError()
    {
        if (\extension_loaded('xdebug')) {
            \xdebug_disable();
        }
        eval('class FatalTest {}');
    }
}
\class_alias('MolliePrefix\\FatalTest', 'FatalTest', \false);
