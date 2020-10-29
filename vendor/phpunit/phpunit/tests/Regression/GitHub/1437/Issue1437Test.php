<?php

namespace MolliePrefix;

class Issue1437Test extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testFailure()
    {
        \ob_start();
        $this->assertTrue(\false);
    }
}
\class_alias('MolliePrefix\\Issue1437Test', 'MolliePrefix\\Issue1437Test', \false);
