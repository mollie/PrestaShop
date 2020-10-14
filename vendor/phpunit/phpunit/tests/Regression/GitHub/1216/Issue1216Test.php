<?php

namespace MolliePrefix;

class Issue1216Test extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testConfigAvailableInBootstrap()
    {
        $this->assertTrue($_ENV['configAvailableInBootstrap']);
    }
}
\class_alias('MolliePrefix\\Issue1216Test', 'Issue1216Test', \false);
