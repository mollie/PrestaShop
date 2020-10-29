<?php

namespace MolliePrefix;

class IniTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testIni()
    {
        $this->assertEquals('application/x-test', \ini_get('default_mimetype'));
    }
}
\class_alias('MolliePrefix\\IniTest', 'MolliePrefix\\IniTest', \false);
